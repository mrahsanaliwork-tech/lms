<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Instructor;
use App\Notifications\AssignmentGraded as AssignmentGradedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AssignmentSubmissionController extends Controller
{
    private function instructor(): Instructor
    {
        return Instructor::where('user_id', Auth::id())->firstOrFail();
    }

    public function grade(Assignment $assignment, AssignmentSubmission $submission)
    {
        abort_if($assignment->instructor_id !== $this->instructor()->id, 403);
        abort_if($submission->assignment_id !== $assignment->id, 404);

        $submission->load('student.user');

        return Inertia::render('Instructor/Assignments/Grade', [
            'assignment' => [
                'id'        => $assignment->id,
                'title'     => $assignment->title,
                'max_marks' => $assignment->max_marks,
            ],
            'submission' => [
                'id'           => $submission->id,
                'student_name' => $submission->student?->user?->name,
                'submitted_at' => $submission->submitted_at?->toDateTimeString(),
                'is_late'      => $submission->submitted_at && $assignment->due_date
                                    && $submission->submitted_at->gt($assignment->due_date),
                'notes'        => $submission->notes,
                'file_url'     => $submission->file_path ? asset('storage/' . $submission->file_path) : null,
                'file_name'    => $submission->file_path ? basename($submission->file_path) : null,
                'grade'        => $submission->grade,
                'feedback'     => $submission->feedback,
                'graded_at'    => $submission->graded_at?->toDateTimeString(),
            ],
        ]);
    }

    public function saveGrade(Request $request, Assignment $assignment, AssignmentSubmission $submission)
    {
        abort_if($assignment->instructor_id !== $this->instructor()->id, 403);
        abort_if($submission->assignment_id !== $assignment->id, 404);

        $data = $request->validate([
            'grade'    => "required|numeric|min:0|max:{$assignment->max_marks}",
            'feedback' => 'nullable|string|max:2000',
        ]);

        $submission->update([
            'grade'     => $data['grade'],
            'feedback'  => $data['feedback'] ?? null,
            'graded_at' => now(),
        ]);

        // Notify student via email (queued)
        $student = $submission->student()->with('user')->first();
        if ($student?->user) {
            $student->user->notify(
                new AssignmentGradedNotification($assignment, $submission)
            );
        }

        return redirect()
            ->route('instructor.assignments.show', $assignment->id)
            ->with('success', 'Grade saved and student notified.');
    }
}
