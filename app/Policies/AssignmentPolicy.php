<?php

namespace App\Policies;

use App\Models\Assignment;
use App\Models\User;

class AssignmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['SuperAdmin', 'Admin', 'Instructor', 'TeachingAssistant', 'Student']);
    }

    public function view(User $user, Assignment $assignment): bool
    {
        if ($user->hasAnyRole(['SuperAdmin', 'Admin'])) return true;
        if ($user->hasAnyRole(['Instructor', 'TeachingAssistant'])) {
            return $assignment->course?->instructor?->user_id === $user->id;
        }
        // Students can view assignments for courses they're enrolled in
        if ($user->hasRole('Student')) {
            return $user->student?->enrollments()
                ->where('course_id', $assignment->course_id)
                ->exists() ?? false;
        }
        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['SuperAdmin', 'Admin', 'Instructor']);
    }

    public function update(User $user, Assignment $assignment): bool
    {
        if ($user->hasAnyRole(['SuperAdmin', 'Admin'])) return true;
        return $user->hasRole('Instructor')
            && $assignment->course?->instructor?->user_id === $user->id;
    }

    public function delete(User $user, Assignment $assignment): bool
    {
        return $user->hasAnyRole(['SuperAdmin', 'Admin']);
    }

    public function grade(User $user, Assignment $assignment): bool
    {
        if ($user->hasAnyRole(['SuperAdmin', 'Admin'])) return true;
        return $user->hasAnyRole(['Instructor', 'TeachingAssistant'])
            && $assignment->course?->instructor?->user_id === $user->id;
    }
}
