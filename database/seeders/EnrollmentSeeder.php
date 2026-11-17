<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Database\Seeder;

class EnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        $students = Student::all();
        $courses  = Course::where('status', 'published')->get();

        if ($students->isEmpty() || $courses->isEmpty()) {
            return;
        }

        // Each student enrolls in 2-4 random courses
        foreach ($students as $student) {
            $count           = rand(2, 4);
            $selectedCourses = $courses->shuffle()->take($count);

            foreach ($selectedCourses as $course) {
                $alreadyEnrolled = Enrollment::where('student_id', $student->id)
                    ->where('course_id', $course->id)
                    ->exists();

                if ($alreadyEnrolled) continue;

                $progress = rand(0, 100);
                $status   = $progress === 100 ? 'completed' : 'active';

                Enrollment::create([
                    'student_id'  => $student->id,
                    'course_id'   => $course->id,
                    'enrolled_at' => now()->subDays(rand(1, 180)),
                    'status'      => $status,
                    'progress'    => $progress,
                ]);
            }
        }
    }
}
