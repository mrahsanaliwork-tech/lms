<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $students = [
            ['name' => 'Emma Wilson',      'email' => 'emma@student.com'],
            ['name' => 'James Thompson',   'email' => 'james@student.com'],
            ['name' => 'Olivia Martinez',  'email' => 'olivia@student.com'],
            ['name' => 'Noah Anderson',    'email' => 'noah@student.com'],
            ['name' => 'Ava Jackson',      'email' => 'ava@student.com'],
            ['name' => 'Liam White',       'email' => 'liam@student.com'],
            ['name' => 'Sophia Harris',    'email' => 'sophia@student.com'],
            ['name' => 'Mason Clark',      'email' => 'mason@student.com'],
            ['name' => 'Isabella Lewis',   'email' => 'isabella@student.com'],
            ['name' => 'Ethan Robinson',   'email' => 'ethan@student.com'],
            ['name' => 'Mia Walker',       'email' => 'mia@student.com'],
            ['name' => 'Lucas Hall',       'email' => 'lucas@student.com'],
            ['name' => 'Charlotte Allen',  'email' => 'charlotte@student.com'],
            ['name' => 'Oliver Young',     'email' => 'oliver@student.com'],
            ['name' => 'Amelia King',      'email' => 'amelia@student.com'],
            ['name' => 'Elijah Scott',     'email' => 'elijah@student.com'],
            ['name' => 'Harper Green',     'email' => 'harper@student.com'],
            ['name' => 'James Baker',      'email' => 'james2@student.com'],
            ['name' => 'Evelyn Nelson',    'email' => 'evelyn@student.com'],
            ['name' => 'Logan Carter',     'email' => 'logan@student.com'],
        ];

        foreach ($students as $index => $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'     => $data['name'],
                    'password' => Hash::make('password'),
                    'status'   => 'active',
                ]
            );
            $user->assignRole('Student');

            Student::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'student_id'      => 'STU-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                    'enrollment_date' => now()->subDays(rand(10, 365)),
                    'status'          => 'active',
                ]
            );
        }
    }
}
