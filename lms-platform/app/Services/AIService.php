<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class AIService
{
    // Per-token cost estimates (USD) for cost tracking
    private const COST_PER_TOKEN = [
        'qwen/qwen3.8-27b'        => ['input' => 0.00000020, 'output' => 0.00000020],
        'openai/gpt-oss-20b'      => ['input' => 0.00000010, 'output' => 0.00000010],
        'openai/gpt-oss-120b'     => ['input' => 0.00000040, 'output' => 0.00000040],
        'allam-2-7b'              => ['input' => 0.00000005, 'output' => 0.00000005],
    ];

    private string $model;
    private string $apiKey;
    private string $baseUrl = 'https://api.groq.com/openai/v1';

    public function __construct()
    {
        $this->apiKey = config('services.groq.api_key', '');
        $this->model  = config('services.groq.model', 'qwen/qwen3.8-27b');
    }

    // ── Public API ─────────────────────────────────────────────────

    /**
     * Generate quiz questions from lesson content.
     *
     * @return array<int, array{question: string, type: string, options: array, correct_answer: string, explanation: string}>
     */
    public function generateQuiz(string $content, int $count = 5, string $difficulty = 'medium'): array
    {
        $prompt = <<<PROMPT
You are an expert educator. Generate exactly {$count} quiz questions from the content below.
Difficulty: {$difficulty}.

Rules:
- Mix question types: MCQ (multiple choice), truefalse (true/false), and short answer.
- For MCQ: provide exactly 4 options, mark the correct one.
- For truefalse: correct_answer must be "True" or "False".
- For short: provide a model answer.
- Return ONLY a valid JSON array — no markdown, no commentary.

Schema per question:
{"question":"...", "type":"mcq|truefalse|short", "options":["A","B","C","D"] or null, "correct_answer":"...", "explanation":"...", "marks":1}

Content:
{$content}
PROMPT;

        $raw = $this->complete($prompt, 2000);

        // Strip markdown fences if present
        $json = preg_replace('/^```(?:json)?\s*|\s*```$/s', '', trim($raw));
        $questions = json_decode($json, true);

        if (!is_array($questions)) {
            throw new RuntimeException('AI returned invalid JSON for quiz generation.');
        }

        return $questions;
    }

    /**
     * Generate a marketing course description.
     */
    public function generateCourseDescription(string $title, string $topic): string
    {
        $prompt = <<<PROMPT
Write a compelling course description for an online LMS course.
Title: "{$title}"
Topic: "{$topic}"

Requirements:
- 2-3 paragraphs, persuasive and informative.
- Mention what students will learn and who it is for.
- Professional tone, no filler phrases.
- Return plain text only, no markdown.
PROMPT;

        return trim($this->complete($prompt, 400));
    }

    /**
     * Summarise a lesson's content into key bullet points.
     */
    public function generateLessonSummary(string $content): string
    {
        $prompt = <<<PROMPT
Summarise the following lesson content into concise, numbered key points a student can review quickly.
Return plain text only, no markdown headers.

Content:
{$content}
PROMPT;

        return trim($this->complete($prompt, 600));
    }

    /**
     * Analyse a student's performance data and return a natural-language insight.
     *
     * @param array{quiz_scores: array, assignment_grades: array, completion_rate: float} $data
     */
    public function analyzeStudentPerformance(array $data): string
    {
        $json  = json_encode($data, JSON_PRETTY_PRINT);
        $prompt = <<<PROMPT
You are an educational data analyst. Given the following student performance data, write a brief personalised analysis (3-5 sentences).
Mention strengths, areas to improve, and one actionable recommendation.
Return plain text only.

Data:
{$json}
PROMPT;

        return trim($this->complete($prompt, 400));
    }

    /**
     * Multi-turn chat with an optional course context injected as system prompt.
     *
     * @param array<int, array{role: string, content: string}> $messages
     */
    public function chat(array $messages, string $context = ''): string
    {
        $system = "You are an expert AI tutor helping students in an online LMS.";
        if ($context) {
            $system .= "\n\nCourse context:\n{$context}";
        }
        $system .= "\nBe concise, accurate, and encouraging. Use plain text — no markdown unless the student asks.";

        return trim($this->completeChat($messages, $system, 800));
    }

    // ── Internal helpers ───────────────────────────────────────────

    /**
     * Single-turn completion (wraps as user message).
     */
    private function complete(string $prompt, int $maxTokens = 800): string
    {
        return $this->completeChat(
            [['role' => 'user', 'content' => $prompt]],
            '',
            $maxTokens
        );
    }

    /**
     * Core chat completion — calls OpenAI, handles errors, returns content string.
     * Callers are responsible for logging (they have context the service doesn't).
     */
    public function completeChat(array $messages, string $systemPrompt = '', int $maxTokens = 800): string
    {
        if (!$this->apiKey) {
            throw new RuntimeException('GROQ_API_KEY is not configured.');
        }

        $payload = [
            'model'      => $this->model,
            'max_tokens' => $maxTokens,
            'messages'   => $systemPrompt
                ? array_merge([['role' => 'system', 'content' => $systemPrompt]], $messages)
                : $messages,
        ];

        $response = Http::withToken($this->apiKey)
            ->timeout(30)
            ->retry(2, 1000, fn ($e) => $this->isRetryable($e))
            ->post("{$this->baseUrl}/chat/completions", $payload);

        if ($response->failed()) {
            $status = $response->status();
            $body   = $response->json();
            $msg    = $body['error']['message'] ?? 'Unknown OpenAI error';

            Log::error('OpenAI API error', ['status' => $status, 'message' => $msg]);

            if ($status === 429) {
                throw new RuntimeException('AI service is busy. Please try again in a moment.');
            }
            if ($status >= 500) {
                throw new RuntimeException('OpenAI is temporarily unavailable. Please try again.');
            }

            throw new RuntimeException("AI error: {$msg}");
        }

        $data = $response->json();

        return $data['choices'][0]['message']['content'] ?? '';
    }

    /**
     * Extract token usage from a raw OpenAI response array for logging.
     *
     * @return array{tokens: int, cost: float, model: string}
     */
    public function extractUsage(array $responseData): array
    {
        $usage        = $responseData['usage'] ?? [];
        $inputTokens  = $usage['prompt_tokens'] ?? 0;
        $outputTokens = $usage['completion_tokens'] ?? 0;
        $model        = $responseData['model'] ?? $this->model;

        $rates = self::COST_PER_TOKEN[$model]
            ?? self::COST_PER_TOKEN[$this->model]
            ?? ['input' => 0, 'output' => 0];

        $cost = ($inputTokens * $rates['input']) + ($outputTokens * $rates['output']);

        return [
            'tokens' => $inputTokens + $outputTokens,
            'cost'   => round($cost, 6),
            'model'  => $model,
        ];
    }

    public function getModel(): string
    {
        return $this->model;
    }

    private function isRetryable(\Throwable $e): bool
    {
        // Retry on network errors, not on auth/validation errors
        if ($e instanceof \Illuminate\Http\Client\RequestException) {
            $status = $e->response?->status() ?? 0;
            return $status === 429 || $status >= 500;
        }
        return false;
    }
}
