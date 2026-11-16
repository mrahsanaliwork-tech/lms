<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Services\CertificateService;
use Illuminate\Http\Request;
use Throwable;

class LessonProgressController extends Controller
{
    public function __construct(private CertificateService $certificateService) {}

    public function complete(Request $request, Lesson $lesson)
    {
        $student = $request->user()->student;
        abort_if(!$student, 403);

        // Record lesson completion (idempotent)
        LessonProgress::firstOrCreate(
            ['student_id' => $student->id, 'lesson_id' => $lesson->id],
            ['completed_at' => now(), 'time_spent' => $request->integer('time_spent', 0)]
        )->update(['completed_at' => now()]);

        // Recalculate enrollment progress
        $course = $lesson->module->course;

        $enrollment = Enrollment::where('student_id', $student->id)
            ->where('course_id', $course->id)
            ->first();

        if (!$enrollment) {
            return response()->json(['progress' => 0]);
        }

        $totalLessons = $course->modules()->withCount('lessons')->get()->sum('lessons_count');
        $completedLessons = LessonProgress::where('student_id', $student->id)
            ->whereHas('lesson.module', fn($q) => $q->where('course_id', $course->id))
            ->whereNotNull('completed_at')
            ->count();

        $progress = $totalLessons > 0
            ? (int) round(($completedLessons / $totalLessons) * 100)
            : 0;

        $enrollment->update(['progress' => $progress]);

        // Generate certificate when course is 100% complete
        if ($progress === 100 && $enrollment->status !== 'completed') {
            $enrollment->update(['status' => 'completed']);

            try {
                $this->certificateService->generate($student, $course);
            } catch (Throwable) {}
        }

        return response()->json(['progress' => $progress]);
    }
}
