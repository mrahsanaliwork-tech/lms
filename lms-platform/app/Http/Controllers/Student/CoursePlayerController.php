<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CoursePlayerController extends Controller
{
    public function show(Request $request, Course $course, ?Lesson $lesson = null)
    {
        $student = $request->user()->student;
        abort_if(!$student, 403);

        // Verify enrollment
        $enrollment = Enrollment::where('student_id', $student->id)
            ->where('course_id', $course->id)
            ->first();
        abort_if(!$enrollment, 403, 'You are not enrolled in this course.');

        // Load full course structure
        $modules = $course->modules()
            ->with('lessons:id,module_id,title,type,content_url,duration,order,is_free')
            ->get();

        // Completed lesson IDs for this student in this course
        $completedIds = LessonProgress::where('student_id', $student->id)
            ->whereHas('lesson.module', fn($q) => $q->where('course_id', $course->id))
            ->whereNotNull('completed_at')
            ->pluck('lesson_id')
            ->toArray();

        // Default to first lesson if none selected
        $allLessons = $modules->flatMap->lessons;
        if (!$lesson) {
            // Try to resume at first incomplete
            $lesson = $allLessons->first(fn($l) => !in_array($l->id, $completedIds))
                ?? $allLessons->first();
        }

        abort_if(!$lesson, 404, 'No lessons found for this course.');

        // Build flat ordered lesson list for prev/next
        $flatLessons = $allLessons->values();
        $currentIndex = $flatLessons->search(fn($l) => $l->id === $lesson->id);
        $prevLesson = $currentIndex > 0 ? $flatLessons[$currentIndex - 1] : null;
        $nextLesson = $currentIndex < $flatLessons->count() - 1 ? $flatLessons[$currentIndex + 1] : null;

        return Inertia::render('Student/CoursePlayer', [
            'course' => [
                'id'       => $course->id,
                'title'    => $course->title,
                'thumbnail'=> $course->thumbnail ? Storage::url($course->thumbnail) : null,
            ],
            'modules' => $modules->map(fn($m) => [
                'id'    => $m->id,
                'title' => $m->title,
                'order' => $m->order,
                'lessons' => $m->lessons->map(fn($l) => [
                    'id'          => $l->id,
                    'title'       => $l->title,
                    'type'        => $l->type,
                    'duration'    => $l->duration,
                    'is_free'     => $l->is_free,
                    'completed'   => in_array($l->id, $completedIds),
                ]),
            ]),
            'currentLesson' => [
                'id'          => $lesson->id,
                'title'       => $lesson->title,
                'type'        => $lesson->type,
                'content_url' => $lesson->content_url,
                'duration'    => $lesson->duration,
                'completed'   => in_array($lesson->id, $completedIds),
            ],
            'prevLessonId'   => $prevLesson?->id,
            'nextLessonId'   => $nextLesson?->id,
            'progress'       => $enrollment->progress,
            'completedIds'   => $completedIds,
            'completeRoute'  => route('student.lessons.complete', $lesson->id),
        ]);
    }
}
