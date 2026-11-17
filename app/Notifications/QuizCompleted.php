<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class QuizCompleted extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $quizTitle,
        public readonly int    $score,
        public readonly int    $total,
        public readonly int    $quizId,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $pct = $this->total > 0 ? round($this->score / $this->total * 100) : 0;

        return [
            'type'    => 'quiz_completed',
            'title'   => 'Quiz completed: ' . $this->quizTitle,
            'message' => "You scored {$this->score}/{$this->total} ({$pct}%)",
            'url'     => '/student/quizzes/' . $this->quizId . '/results',
            'icon'    => 'clipboard-check',
            'score'   => $this->score,
            'total'   => $this->total,
        ];
    }
}
