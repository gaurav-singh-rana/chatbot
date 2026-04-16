<?php

namespace App\Http\Controllers;

use App\Models\ChatLog;
use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatController extends Controller
{
    public function chat(Request $request)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:500'],
        ]);

        $query = strtolower(trim($validated['message']));
        $words = preg_split('/\s+/', $query, -1, PREG_SPLIT_NO_EMPTY);
        $intent = $this->detectIntent($query);

        $faqs = Faq::all();
        $bestMatch = null;
        $bestScore = 0;

        foreach ($faqs as $faq) {
            $score = 0;
            $faqKeywords = strtolower((string) $faq->keywords);
            $faqQuestion = strtolower((string) $faq->question);

            foreach ($words as $word) {
                if (strlen($word) < 2) {
                    continue;
                }

                if (str_contains($faqKeywords, $word)) {
                    $score += 2;
                }

                if (str_contains($faqQuestion, $word)) {
                    $score += 1;
                }
            }

            if ($faq->category === $intent) {
                $score += 3;
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = $faq;
            }
        }

        if ($bestMatch && $bestScore >= 3) {
            $reply = $bestMatch->answer;
            $source = 'FAQ';

            $this->logChat($query, $reply, $source);

            return response()->json([
                'reply' => $reply,
                'source' => $source,
            ]);
        }

        try {
            $apiKey = config('services.gemini.key') ?? env('GEMINI_API_KEY');
            $url = 'https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash-lite:generateContent?key='.$apiKey;
            $prompt = $this->buildPrompt($query, $intent);

            $response = Http::post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0.3,
                    'maxOutputTokens' => 100,
                ],
            ]);

            $data = $response->json();

            if ($response->failed()) {
                $reply = $this->extractGeminiError($data);
                $source = 'Error';
            } elseif (! empty($data['candidates'][0]['content']['parts'][0]['text'])) {
                $reply = trim($data['candidates'][0]['content']['parts'][0]['text']);
                $source = 'AI';
            } elseif (! empty($data['promptFeedback']['blockReason'])) {
                $reply = 'The AI response was blocked by safety filters. Please try a simpler sales-related question.';
                $source = 'Blocked';
            } elseif (! empty($data['candidates'][0]['finishReason'])) {
                $reply = 'AI returned no final text for this query. Please try rephrasing the question.';
                $source = 'Empty';
            } else {
                $reply = 'Please ask about plans, sales pitch, objections, or process steps.';
                $source = 'Fallback';
            }
        } catch (\Exception $e) {
            $reply = 'System is busy right now. Please try again.';
            $source = 'Error';
        }

        $this->logChat($query, $reply, $source);

        return response()->json([
            'reply' => $reply,
            'source' => $source,
        ]);
    }

    private function detectIntent($query)
    {
        if (preg_match('/expensive|costly|price high|too much|mehenga/', $query)) {
            return 'objection';
        }

        if (preg_match('/plan|price|postpaid|prepaid|wifi|fiber|ott|benefits/', $query)) {
            return 'plan';
        }

        if (preg_match('/process|activation|sim|kyc|booking|eligibility|port|steps/', $query)) {
            return 'process';
        }

        if (preg_match('/pitch|sell|convince|how to sell|sales talk/', $query)) {
            return 'sales';
        }

        return 'general';
    }

    private function buildPrompt($query, $intent)
    {
        $base = 'You are a field sales support assistant for a telecom platform.

Rules:
- Maximum 2 lines
- No greeting
- Keep the answer simple, practical, and sales-focused';

        $context = 'The platform supports postpaid, prepaid, Wi-Fi, OTT benefits, objection handling, booking, eligibility, activation, and sales pitch guidance.';

        switch ($intent) {
            case 'plan':
                $task = 'Answer plan-related questions with benefits, value, and customer-friendly clarity.';
                break;
            case 'objection':
                $task = 'Handle objections and help the sales agent justify value in simple words.';
                break;
            case 'process':
                $task = 'Give short process steps for booking, eligibility, SIM, or activation queries.';
                break;
            case 'sales':
                $task = 'Give a short, strong sales pitch the agent can use on ground.';
                break;
            default:
                $task = 'Answer only field-sales related telecom queries in a helpful and simple way.';
                break;
        }

        return $base."

Context:
$context

Task:
$task

User: $query
Answer:";
    }

    private function extractGeminiError($data)
    {
        $message = $data['error']['message'] ?? null;

        if ($message) {
            return 'Gemini API error: '.$message;
        }

        return 'Gemini API request failed. Please check API key, quota, or model access.';
    }

    private function logChat($query, $reply, $source)
    {
        try {
            ChatLog::create([
                'user_query' => $query,
                'response' => $reply,
                'source' => $source,
            ]);
        } catch (\Exception $e) {
            
        }
    }
}
