<?php

namespace App\Policies;

use App\Models\Quiz;
use App\Models\User;

class QuizPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['SuperAdmin', 'Admin', 'Instructor', 'TeachingAssistant']);
    }

    public function view(User $user, Quiz $quiz): bool
    {
        if ($user->hasAnyRole(['SuperAdmin', 'Admin'])) return true;
        return $user->hasAnyRole(['Instructor', 'TeachingAssistant'])
            && $quiz->course?->instructor?->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['SuperAdmin', 'Admin', 'Instructor']);
    }

    public function update(User $user, Quiz $quiz): bool
    {
        if ($user->hasAnyRole(['SuperAdmin', 'Admin'])) return true;
        return $user->hasRole('Instructor')
            && $quiz->course?->instructor?->user_id === $user->id;
    }

    public function delete(User $user, Quiz $quiz): bool
    {
        return $user->hasAnyRole(['SuperAdmin', 'Admin']);
    }

    public function attempt(User $user, Quiz $quiz): bool
    {
        return $user->hasRole('Student')
            && $quiz->status === 'published';
    }
}
