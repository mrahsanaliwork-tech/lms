<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

class StudentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['SuperAdmin', 'Admin', 'Instructor', 'OrganizationManager']);
    }

    public function view(User $user, Student $student): bool
    {
        if ($user->hasAnyRole(['SuperAdmin', 'Admin'])) return true;
        // A student can view their own profile
        if ($user->hasRole('Student')) return $student->user_id === $user->id;
        // Org managers can view students in their org
        if ($user->hasRole('OrganizationManager')) {
            return $user->instructor?->organization_id === $student->organization_id;
        }
        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['SuperAdmin', 'Admin']);
    }

    public function update(User $user, Student $student): bool
    {
        if ($user->hasAnyRole(['SuperAdmin', 'Admin'])) return true;
        return $user->hasRole('Student') && $student->user_id === $user->id;
    }

    public function delete(User $user, Student $student): bool
    {
        return $user->hasRole('SuperAdmin');
    }

    public function impersonate(User $user, Student $student): bool
    {
        return $user->hasRole('SuperAdmin');
    }
}
