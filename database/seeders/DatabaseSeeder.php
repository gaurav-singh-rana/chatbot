<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Faq::query()->delete();

        $faqs = [
            [
                'category' => 'plan',
                'question' => 'What are the benefits of a postpaid plan?',
                'answer' => 'Postpaid plans offer monthly billing, better data value, OTT benefits, and hassle-free continuity for heavy users.',
                'keywords' => 'postpaid plan price benefits monthly bill ott data',
            ],
            [
                'category' => 'plan',
                'question' => 'Why should a customer choose Wi-Fi or fiber?',
                'answer' => 'Wi-Fi plans give stable high-speed internet, better family usage, and strong value for work, streaming, and smart devices.',
                'keywords' => 'wifi fiber broadband speed internet home family streaming',
            ],
            [
                'category' => 'plan',
                'question' => 'What is the value of OTT bundled plans?',
                'answer' => 'OTT bundled plans save money by combining data and entertainment benefits in one simple package.',
                'keywords' => 'ott bundle netflix streaming entertainment combo value',
            ],
            [
                'category' => 'sales',
                'question' => 'Give me a sales pitch for a postpaid plan.',
                'answer' => 'You get one reliable monthly plan with strong data, calling benefits, and premium add-ons, so there is no daily recharge stress.',
                'keywords' => 'sales pitch postpaid sell convince talk track',
            ],
            [
                'category' => 'sales',
                'question' => 'How should I pitch Wi-Fi to a family customer?',
                'answer' => 'Position Wi-Fi as one connection for work, study, OTT, and multiple devices, which makes it more convenient than repeated mobile recharges.',
                'keywords' => 'pitch wifi family home broadband sell convenience',
            ],
            [
                'category' => 'objection',
                'question' => 'Customer says the plan is expensive.',
                'answer' => 'Acknowledge the concern, then highlight total value: data, calling, OTT, and convenience together often cost less than separate spends.',
                'keywords' => 'expensive costly high price objection value convince',
            ],
            [
                'category' => 'objection',
                'question' => 'Customer says they do not need postpaid.',
                'answer' => 'Explain that postpaid is useful for uninterrupted service, better bundled value, and predictable monthly usage without recharge gaps.',
                'keywords' => 'do not need postpaid objection monthly value uninterrupted',
            ],
            [
                'category' => 'process',
                'question' => 'What is the SIM activation process?',
                'answer' => 'Confirm documents, complete KYC, submit the request, and inform the customer that activation usually happens after verification.',
                'keywords' => 'sim activation process kyc steps verification',
            ],
            [
                'category' => 'process',
                'question' => 'How do I check customer eligibility?',
                'answer' => 'Check the service area, verify documents, confirm plan availability, and then proceed with booking or onboarding.',
                'keywords' => 'eligibility check process booking documents service area',
            ],
            [
                'category' => 'process',
                'question' => 'How does booking work?',
                'answer' => 'Collect the customer details, confirm the selected plan, verify eligibility, and submit the booking request for processing.',
                'keywords' => 'booking process customer details plan request submit',
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::create($faq);
        }

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
