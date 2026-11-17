<?php

namespace App\Jobs;

use App\Services\AIService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Dispatched for heavy AI operations (quiz generation, description, summary, student analysis).
 * Interactive chat remains synchronous for UX; this handles bulk/background tasks.
 */
class ProcessAiRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 120;

    public function __construct(
        private readonly string $type,
        private readonly array  $payload,
        private readonly ?int   $studentId = null,
        private readonly ?int   $courseId  = null,
    ) {
        $this->onQueue('ai');
    }

    public function handle(AIService $ai): void
    {
        match ($this->type) {
            'quiz'        => $ai->generateQuiz($this->payload['content'], $this->payload['count'] ?? 5, $this->payload['difficulty'] ?? 'medium'),
            'description' => $ai->generateDescription($this->payload['title'], $this->payload['topics'] ?? []),
            'summary'     => $ai->generateSummary($this->payload['content']),
            'analyze'     => $ai->analyzeStudent($this->payload),
            default       => null,
        };
    }
}
