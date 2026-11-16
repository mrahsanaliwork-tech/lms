<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AssignmentController extends Controller
{
    private function student(): Student
    {
        return Student::where('user_id', Auth::id())->firstOrFail();
    }

    public function index()
    {
        $student = $this->student();

        // Get assignments for courses the student is enrolled in
        $assignments = Assignment::with('course')
            ->whereHas('course.enrollments', fn ($q) => $q->where('student_id', $student->id))
            ->withCount('submissions')
            ->get()
            ->map(function ($a) use ($student) {
                $submission = $a->submissions()->where('student_id', $student->id)->first();
                return [
                    'id'          => $a->id,
                    'title'       => $a->title,
                    'course'      => $a->course?->title,
                    'due_date'    => $a->due_date?->toDateTimeString(),
                    'is_overdue'  => $a->due_date?->isPast(),
                    'max_marks'   => $a->max_marks,
                    'allow_late'  => $a->allow_late_submission,
                    'submitted'   => $submission !== null,
                    'grade'       => $submission?->grade,
                    'graded'      => $submission?->graded_at !== null,
                    'submission_id' => $submission?->id,
                ];
            });

        $upcoming = $assignments->filter(fn ($a) => !$a['is_overdue'] && !$a['submitted'])->values();
        $submitted = $assignments->filter(fn ($a) => $a['submitted'])->values();
        $missed    = $assignments->filter(fn ($a) => $a['is_overdue'] && !$a['submitted'])->values();

        return Inertia::render('Student/Assignments/Index', compact('upcoming', 'submitted', 'missed'));
    }

    public function show(Assignment $assignment)
    {
        $student    = $this->student();
        $submission = $assignment->submissions()->where('student_id', $student->id)->first();

        return Inertia::render('Student/Assignments/Submit', [
            'assignment' => [
                'id'          => $assignment->id,
                'title'       => $assignment->title,
                'description' => $assignment->description,
                'due_date'    => $assignment->due_date?->toDateTimeString(),
                'is_overdue'  => $assignment->due_date?->isPast(),
                'max_marks'   => $assignment->max_marks,
                'allow_late'  => $assignment->allow_late_submission,
                'course'      => $assignment->course?->title,
            ],
            'submission' => $submission ? [
                'id'           => $submission->id,
                'submitted_at' => $submission->submitted_at?->toDateTimeString(),
                'is_late'      => $submission->submitted_at && $assignment->due_date
                                    && $submission->submitted_at->gt($assignment->due_date),
                'notes'        => $submission->notes,
                'file_name'    => $submission->file_path ? basename($submission->file_path) : null,
                'file_url'     => $submission->file_path ? asset('storage/' . $submission->file_path) : null,
                'grade'        => $submission->grade,
                'feedback'     => $submission->feedback,
                'graded_at'    => $submission->graded_at?->toDateTimeString(),
            ] : null,
        ]);
    }
}
