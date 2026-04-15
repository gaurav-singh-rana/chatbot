<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Models\Faq;
use App\Models\ChatLog;
use OpenAI\Laravel\Facades\OpenAI;

class ChatController extends Controller
{

public function chat(Request $request)
{
    $query = strtolower(trim($request->message));
    $words = explode(' ', $query);

    
    $intent = $this->detectIntent($query);

    
    $faqs = Faq::get();

    $bestMatch = null;
    $bestScore = 0;

    foreach ($faqs as $faq) {
        $score = 0;

        foreach ($words as $word) {
            if(strlen($word) < 2) continue;

            if (str_contains($faq->keywords, $word)) $score += 2;
            if (str_contains($faq->question, $word)) $score += 1;
        }

        // Intent boost
        if ($faq->category === $intent) {
            $score += 3;
        }

        if ($score > $bestScore) {
            $bestScore = $score;
            $bestMatch = $faq;
        }
    }

   
    if ($bestMatch && $bestScore >= 3) {
        $this->logChat($query, $bestMatch->answer, 'Database');

        return response()->json([
            'reply' => $bestMatch->answer
        ]);
    }

    
    try {
        $apiKey = config('services.gemini.key') ?? env('GEMINI_API_KEY');

        $url = "https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash-lite:generateContent?key=" . $apiKey;

        $intent = $this->detectIntent($query);

        $prompt = $this->buildPrompt($query, $intent);

        $response = Http::post($url, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.3,
                'maxOutputTokens' => 100
            ]
        ]);

        $data = $response->json();

        if (!empty($data['candidates'][0]['content']['parts'][0]['text'])) {
            $reply = $data['candidates'][0]['content']['parts'][0]['text'];
            $source = 'AI';
        } else {
            $reply = "Ask about plans, offers, or activation.";
            $source = 'Fallback';
        }

    } catch (\Exception $e) {
        $reply = "System busy. Try again.";
        $source = 'Error';
    }

    $this->logChat($query, $reply, $source);

    return response()->json([
        'reply' => $reply
        
    ]);
}

private function detectIntent($query)
{
    // Objection
    if (preg_match('/mehenga|expensive|costly|high price/', $query)) {
        return 'objection';
    }

    // Plan
    if (preg_match('/plan|price|postpaid|prepaid|wifi|fiber|ott/', $query)) {
        return 'plan';
    }

    // Process
    if (preg_match('/process|activation|sim|kyc|booking|eligibility/', $query)) {
        return 'process';
    }

    // Sales pitch
    if (preg_match('/sell|pitch|kaise beche|convince/', $query)) {
        return 'sales';
    }

    if (preg_match('/offer|offers|discount|deal|promo/', $query)) {
    return 'offer';
    }

    return 'general';
}

private function buildPrompt($query, $intent)
{
    $base = "You are an Airtel field sales assistant.

Rules:
- Max 2 lines
- No greetings
- Be practical and sales-focused";

    $context = "Airtel provides prepaid, postpaid, WiFi (fiber), OTT bundles, cashback offers, SIM activation, and porting services.";

    switch ($intent) {

        case 'plan':
            return $base . "

Context:
$context

Task:
Explain Airtel plan benefits (OTT, data, speed, value)

User: $query
Answer:";

        case 'offer':
            return $base . "

Context:
$context

Task:
Share current Airtel offers like OTT, cashback, extra data in short

User: $query
Answer:";

        case 'objection':
            return $base . "

Context:
$context

Task:
Handle objection and convince customer with value

User: $query
Answer:";

        case 'process':
            return $base . "

Context:
$context

Task:
Give step-by-step process in short

User: $query
Answer:";

        case 'sales':
            return $base . "

Context:
$context

Task:
Give a strong sales pitch to convert customer

User: $query
Answer:";

        default:
            return $base . "

Context:
$context

Task:
Understand the user query and respond in telecom context (plans, offers, services).
If unclear, still try to give helpful Airtel-related info.

User: $query
Answer:";
    }
}

private function logChat($query, $reply, $source) {
    try {
        ChatLog::create([
            'user_query' => $query,
            'response' => $reply,
            'source' => $source
        ]);
    } catch (\Exception $e) {
        
    }
}

}