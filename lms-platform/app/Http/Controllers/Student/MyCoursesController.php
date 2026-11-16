<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\LessonProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class MyCoursesController extends Controller
{
    public function index(Request $request)
    {
        $student = $request->user()->student;
        abort_if(!$student, 403);

        $enrollments = $student->enrollments()
            ->with([
                'course:id,title,slug,thumbnail,level,language,duration,instructor_id',
                'course.instructor.user:id,name',
                'course.modules.lessons:id,module_id,title,type,duration',
            ])
            ->latest()
            ->get();

        // For each course find the last-touched lesson
        $completedLessonIds = LessonProgress::where('student_id', $student->id)
            ->whereNotNull('completed_at')
            ->pluck('lesson_id')
            ->flip();

        $courses = $enrollments->map(function ($e) use ($completedLessonIds) {
            $course = $e->course;
            if (!$course) return null;

            $allLessons = $course->modules->flatMap->lessons;
            $totalLessons = $allLessons->count();

            // First incomplete lesson = continue point
            $nextLesson = $allLessons->first(fn($l) => !isset($completedLessonIds[$l->id]));

            return [
                'enrollment_id'  => $e->id,
                'course_id'      => $course->id,
                'title'          => $course->title,
                'thumbnail'      => $course->thumbnail ? Storage::url($course->thumbnail) : null,
                'level'          => $course->level,
                'language'       => $course->language,
                'duration'       => $course->duration,
                'instructor'     => $course->instructor?->user?->name,
                'progress'       => $e->progress,
                'status'         => $e->status,
                'enrolled_at'    => $e->enrolled_at?->format('M j, Y'),
                'total_lessons'  => $totalLessons,
                'completed_lessons' => $completedLessonIds->count(),
                'next_lesson_id' => $nextLesson?->id,
                'next_lesson_title' => $nextLesson?->title,
            ];
        })->filter()->values();

        return Inertia::render('Student/MyCourses', [
            'courses' => $courses,
        ]);
    }
}
