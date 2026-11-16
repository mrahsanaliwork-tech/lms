<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            CategorySeeder::class,
            InstructorSeeder::class,
            CourseSeeder::class,
            StudentSeeder::class,
            EnrollmentSeeder::class,
        ]);

        // Ensure SuperAdmin exists
        $admin = User::firstOrCreate(
            ['email' => 'admin@lms.com'],
            [
                'name'     => 'Super Admin',
                'password' => Hash::make('password'),
                'status'   => 'active',
            ]
        );

        $admin->assignRole('SuperAdmin');
    }
}
