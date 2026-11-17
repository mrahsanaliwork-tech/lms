<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\LessonProgress;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $student = $request->user()->student;
        abort_if(!$student, 403, 'Student profile not found.');

        // ── Enrolled courses with progress ───────────────────────────
        $enrollments = $student->enrollments()
            ->with('course:id,title,slug,thumbnail,level,duration,instructor_id')
            ->latest()
            ->get();

        $courseIds = $enrollments->pluck('course_id');

        // ── Last accessed lesson (continue learning) ─────────────────
        $lastProgress = LessonProgress::where('student_id', $student->id)
            ->with(['lesson:id,module_id,title,type,content_url', 'lesson.module:id,course_id,title'])
            ->whereNotNull('completed_at')
            ->latest('updated_at')
            ->first();

        $continueLesson = null;
        if ($lastProgress) {
            $courseId = $lastProgress->lesson?->module?->course_id;
            $enrollment = $enrollments->firstWhere('course_id', $courseId);
            if ($enrollment && $enrollment->course) {
                $continueLesson = [
                    'lesson_id'    => $lastProgress->lesson_id,
                    'lesson_title' => $lastProgress->lesson->title,
                    'course_id'    => $courseId,
                    'course_title' => $enrollment->course->title,
                    'thumbnail'    => $enrollment->course->thumbnail
                        ? Storage::url($enrollment->course->thumbnail) : null,
                    'progress'     => $enrollment->progress,
                ];
            }
        }

        // ── Upcoming assignments (due in 7 days) ─────────────────────
        $upcomingAssignments = Assignment::whereIn('course_id', $courseIds)
            ->where('due_date', '>', now())
            ->where('due_date', '<=', now()->addDays(7))
            ->with('course:id,title')
            ->orderBy('due_date')
            ->get()
            ->map(fn($a) => [
                'id'           => $a->id,
                'title'        => $a->title,
                'course_title' => $a->course?->title,
                'due_date'     => $a->due_date->toISOString(),
                'max_marks'    => $a->max_marks,
            ]);

        // ── Upcoming quizzes (not yet attempted) ─────────────────────
        $attemptedQuizIds = $student->quizAttempts()
            ->whereNotNull('completed_at')
            ->pluck('quiz_id');

        $upcomingQuizzes = Quiz::whereIn('course_id', $courseIds)
            ->where('status', 'published')
            ->whereNotIn('id', $attemptedQuizIds)
            ->with('course:id,title')
            ->limit(5)
            ->get()
            ->map(fn($q) => [
                'id'           => $q->id,
                'title'        => $q->title,
                'course_title' => $q->course?->title,
                'time_limit'   => $q->time_limit,
                'passing_marks'=> $q->passing_marks,
            ]);

        // ── Recent quiz results ──────────────────────────────────────
        $recentQuizResults = $student->quizAttempts()
            ->with('quiz:id,title,course_id,passing_marks', 'quiz.course:id,title')
            ->whereNotNull('completed_at')
            ->latest('completed_at')
            ->limit(5)
            ->get()
            ->map(fn($a) => [
                'id'           => $a->id,
                'quiz_title'   => $a->quiz?->title,
                'course_title' => $a->quiz?->course?->title,
                'score'        => $a->score,
                'total_marks'  => $a->total_marks,
                'passed'       => $a->passed,
                'completed_at' => $a->completed_at?->toISOString(),
            ]);

        // ── Certificates ─────────────────────────────────────────────
        $certificates = $student->certificates()
            ->with('course:id,title')
            ->latest('issued_at')
            ->limit(3)
            ->get()
            ->map(fn($c) => [
                'id'                 => $c->id,
                'certificate_number' => $c->certificate_number,
                'course_title'       => $c->course?->title,
                'issued_at'          => $c->issued_at->format('M j, Y'),
                'download_url'       => route('student.certificates.download', $c->id),
            ]);

        // ── Courses shaped for the frontend ──────────────────────────
        $courses = $enrollments->map(fn($e) => [
            'enrollment_id' => $e->id,
            'course_id'     => $e->course_id,
            'title'         => $e->course?->title,
            'thumbnail'     => $e->course?->thumbnail
                ? Storage::url($e->course->thumbnail) : null,
            'level'         => $e->course?->level,
            'duration'      => $e->course?->duration,
            'progress'      => $e->progress,
            'status'        => $e->status,
            'enrolled_at'   => $e->enrolled_at?->format('M j, Y'),
        ]);

        // ── Stats ─────────────────────────────────────────────────────
        $stats = [
            'total_courses'      => $enrollments->count(),
            'completed_courses'  => $enrollments->where('status', 'completed')->count(),
            'avg_progress'       => $enrollments->count()
                ? (int) round($enrollments->avg('progress')) : 0,
            'total_assignments'  => $upcomingAssignments->count(),
            'total_certificates' => $student->certificates()->count(),
        ];

        return Inertia::render('Student/Dashboard', [
            'stats'              => $stats,
            'courses'            => $courses,
            'continueLesson'     => $continueLesson,
            'upcomingAssignments'=> $upcomingAssignments,
            'upcomingQuizzes'    => $upcomingQuizzes,
            'recentQuizResults'  => $recentQuizResults,
            'certificates'       => $certificates,
        ]);
    }
}
