<?php

namespace Database\Seeders;

use App\Models\Instructor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class InstructorSeeder extends Seeder
{
    public function run(): void
    {
        $instructors = [
            [
                'name'  => 'Sarah Johnson',
                'email' => 'sarah@lms.com',
                'bio'   => 'Full-stack developer with 10+ years building web applications. Specialises in Laravel, Vue.js and cloud architecture.',
                'expertise' => ['Laravel', 'Vue.js', 'AWS', 'Docker'],
                'verification_status' => 'verified',
                'commission_rate' => 70.00,
            ],
            [
                'name'  => 'Dr. Michael Chen',
                'email' => 'michael@lms.com',
                'bio'   => 'PhD in Computer Science, researcher in machine learning and AI with publications in top-tier journals.',
                'expertise' => ['Python', 'Machine Learning', 'TensorFlow', 'Data Science'],
                'verification_status' => 'verified',
                'commission_rate' => 70.00,
            ],
            [
                'name'  => 'Alex Rivera',
                'email' => 'alex@lms.com',
                'bio'   => 'Mobile developer and UI/UX designer with a passion for clean, accessible interfaces.',
                'expertise' => ['React Native', 'Flutter', 'Figma', 'Swift'],
                'verification_status' => 'verified',
                'commission_rate' => 65.00,
            ],
        ];

        foreach ($instructors as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'     => $data['name'],
                    'password' => Hash::make('password'),
                    'status'   => 'active',
                ]
            );
            $user->assignRole('Instructor');

            Instructor::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'bio'                 => $data['bio'],
                    'expertise'           => $data['expertise'],
                    'verification_status' => $data['verification_status'],
                    'commission_rate'     => $data['commission_rate'],
                ]
            );
        }
    }
}
