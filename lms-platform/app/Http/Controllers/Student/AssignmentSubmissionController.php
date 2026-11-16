<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AssignmentSubmissionController extends Controller
{
    private function student(): Student
    {
        return Student::where('user_id', Auth::id())->firstOrFail();
    }

    public function store(Request $request, Assignment $assignment)
    {
        $student = $this->student();

        // Block late submissions if not allowed
        if ($assignment->due_date?->isPast() && !$assignment->allow_late_submission) {
            return back()->withErrors(['file' => 'This assignment no longer accepts submissions.']);
        }

        // Block if already submitted
        $existing = $assignment->submissions()->where('student_id', $student->id)->exists();
        if ($existing) {
            return back()->withErrors(['file' => 'You have already submitted this assignment.']);
        }

        $request->validate([
            'file'  => 'required|file|mimes:pdf,doc,docx,zip|max:20480',
            'notes' => 'nullable|string|max:1000',
        ]);

        $path = $request->file('file')->store(
            "assignments/{$assignment->id}",
            'public'
        );

        AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'student_id'    => $student->id,
            'file_path'     => $path,
            'notes'         => $request->notes,
            'submitted_at'  => now(),
        ]);

        return redirect()
            ->route('student.assignments.show', $assignment->id)
            ->with('success', 'Assignment submitted successfully.');
    }
}
