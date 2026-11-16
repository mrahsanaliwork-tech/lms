<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['SuperAdmin', 'Admin', 'Instructor', 'TeachingAssistant']);
    }

    public function view(User $user, Course $course): bool
    {
        if ($user->hasAnyRole(['SuperAdmin', 'Admin'])) return true;
        // Instructors can only view their own courses
        return $user->hasRole('Instructor') && $course->instructor?->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['SuperAdmin', 'Admin', 'Instructor']);
    }

    public function update(User $user, Course $course): bool
    {
        if ($user->hasAnyRole(['SuperAdmin', 'Admin'])) return true;
        return $user->hasRole('Instructor') && $course->instructor?->user_id === $user->id;
    }

    public function delete(User $user, Course $course): bool
    {
        if ($user->hasRole('SuperAdmin')) return true;
        if ($user->hasRole('Admin')) return $course->enrollments()->count() === 0;
        return false;
    }

    public function publish(User $user, Course $course): bool
    {
        return $user->hasAnyRole(['SuperAdmin', 'Admin']);
    }

    public function approve(User $user, Course $course): bool
    {
        return $user->hasAnyRole(['SuperAdmin', 'Admin']);
    }
}
