<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Users
            'users.view', 'users.create', 'users.edit', 'users.delete',
            // Courses
            'courses.view', 'courses.create', 'courses.edit', 'courses.delete', 'courses.publish',
            // Modules & Lessons
            'modules.manage', 'lessons.manage',
            // Enrollments
            'enrollments.view', 'enrollments.manage',
            // Quizzes
            'quizzes.view', 'quizzes.create', 'quizzes.edit', 'quizzes.delete', 'quizzes.attempt',
            // Assignments
            'assignments.view', 'assignments.create', 'assignments.edit', 'assignments.grade',
            // Attendance
            'attendance.view', 'attendance.manage',
            // Reports
            'reports.view', 'reports.export',
            // Organizations
            'organizations.view', 'organizations.manage',
            // Settings
            'settings.manage',
            // Roles
            'roles.manage',
            // Payments
            'payments.view', 'payments.manage',
            // Notifications
            'notifications.send',
            // AI
            'ai.use',
            // Workflows
            'workflows.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $roles = [
            'SuperAdmin' => Permission::all()->pluck('name')->toArray(),

            'Admin' => [
                'users.view', 'users.create', 'users.edit', 'users.delete',
                'courses.view', 'courses.create', 'courses.edit', 'courses.delete', 'courses.publish',
                'modules.manage', 'lessons.manage',
                'enrollments.view', 'enrollments.manage',
                'quizzes.view', 'quizzes.create', 'quizzes.edit', 'quizzes.delete',
                'assignments.view', 'assignments.create', 'assignments.edit', 'assignments.grade',
                'attendance.view', 'attendance.manage',
                'reports.view', 'reports.export',
                'organizations.view',
                'payments.view', 'payments.manage',
                'notifications.send',
            ],

            'Instructor' => [
                'courses.view', 'courses.create', 'courses.edit', 'courses.publish',
                'modules.manage', 'lessons.manage',
                'enrollments.view',
                'quizzes.view', 'quizzes.create', 'quizzes.edit', 'quizzes.delete',
                'assignments.view', 'assignments.create', 'assignments.edit', 'assignments.grade',
                'attendance.view', 'attendance.manage',
                'reports.view',
                'ai.use',
            ],

            'TeachingAssistant' => [
                'courses.view',
                'enrollments.view',
                'quizzes.view',
                'assignments.view', 'assignments.grade',
                'attendance.view', 'attendance.manage',
                'reports.view',
                'ai.use',
            ],

            'OrganizationManager' => [
                'users.view', 'users.create', 'users.edit',
                'courses.view',
                'enrollments.view', 'enrollments.manage',
                'attendance.view',
                'reports.view', 'reports.export',
                'organizations.view', 'organizations.manage',
                'payments.view',
            ],

            'Student' => [
                'courses.view',
                'quizzes.view', 'quizzes.attempt',
                'assignments.view',
                'ai.use',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($rolePermissions);
        }
    }
}
