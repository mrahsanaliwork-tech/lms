<?php

namespace App\Listeners;

use App\Events\QuizCompleted;

class OnQuizCompleted extends ProcessAutomationTrigger
{
    protected function trigger(): string { return 'quiz.completed'; }

    protected function buildContext(object $event): array
    {
        /** @var QuizCompleted $event */
        $attempt = $event->attempt;
        return [
            'user'       => $event->user,
            'attempt'    => $attempt,
            'quiz'       => $attempt->quiz,
            'score'      => $attempt->score,
            'total'      => $attempt->total_points,
            'passed'     => $attempt->is_passed,
            'quiz_title' => $attempt->quiz?->title,
        ];
    }
}
