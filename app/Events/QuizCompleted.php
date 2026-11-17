<?php

namespace App\Events;

use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QuizCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly User        $user,
        public readonly QuizAttempt $attempt,
    ) {}
}
