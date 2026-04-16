# Sales Support Chatbot

A simple hybrid chatbot built with Laravel for field sales teams. It answers day-to-day sales, product, and process questions using a small FAQ knowledge base first, then falls back to Gemini AI when no strong FAQ match is found.

## Features

- FAQ-first response flow for fast and controlled answers
- AI fallback for flexible handling of unmatched sales queries
- Covers plan details, sales pitch guidance, objection handling, and process queries
- Chat logging for review and improvement
- Simple floating chat widget UI for demo purposes

## Workflow

`User query -> intent detection -> FAQ match -> AI fallback if needed -> response -> chat log`

## Tech Stack

- PHP 8.2
- Laravel 12
- Blade + Bootstrap
- MySQL or SQLite
- Gemini API

## Sample Use Cases

- Plan details: postpaid, prepaid, Wi-Fi, OTT benefits
- Sales pitch guidance
- Objection handling like "plan is expensive"
- Process help like booking, eligibility, and activation

## Local Setup

1. Clone the repository.
2. Install dependencies:

```bash
composer install
```

3. Create your environment file:

```bash
cp .env.example .env
```

4. Add your Gemini API key in `.env`.
5. Generate the application key:

```bash
php artisan key:generate
```

6. Run migrations and seed sample chatbot data:

```bash
php artisan migrate --seed
```

7. Start the Laravel server:

```bash
php artisan serve
```

8. Open the app in your browser:

```text
http://127.0.0.1:8000
```

## Demo Questions

- `What are the benefits of a postpaid plan?`
- `Give me a short sales pitch for Wi-Fi`
- `Customer says the plan is expensive`
- `What is the SIM activation process?`

## Notes

- FAQ answers are returned with source `FAQ`
- AI-generated answers are returned with source `AI`
- Do not commit your real `.env` file or API keys to GitHub
