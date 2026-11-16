<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Instructor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AssignmentController extends Controller
{
    private function instructor(): Instructor
    {
        return Instructor::where('user_id', Auth::id())->firstOrFail();
    }

    private function authorizeOwner(Assignment $assignment): void
    {
        abort_if($assignment->instructor_id !== $this->instructor()->id, 403);
    }

    public function index()
    {
        $instructor = $this->instructor();

        $assignments = Assignment::with('course')
            ->withCount(['submissions', 'submissions as graded_count' => fn ($q) => $q->graded()])
            ->where('instructor_id', $instructor->id)
            ->latest()
            ->paginate(20)
            ->through(fn ($a) => [
                'id'               => $a->id,
                'title'            => $a->title,
                'course'           => $a->course?->title,
                'due_date'         => $a->due_date?->toDateTimeString(),
                'is_overdue'       => $a->due_date?->isPast(),
                'max_marks'        => $a->max_marks,
                'submissions_count'=> $a->submissions_count,
                'graded_count'     => $a->graded_count,
                'pending_count'    => $a->submissions_count - $a->graded_count,
            ]);

        $courses = Course::where('instructor_id', $instructor->id)
            ->where('status', 'published')
            ->get(['id', 'title']);

        return Inertia::render('Instructor/Assignments/Index', compact('assignments', 'courses'));
    }

    public function store(Request $request)
    {
        $instructor = $this->instructor();

        $data = $request->validate([
            'course_id'            => 'required|integer|exists:courses,id',
            'title'                => 'required|string|max:255',
            'description'          => 'nullable|string',
            'due_date'             => 'required|date|after:now',
            'max_marks'            => 'required|integer|min:1',
            'allow_late_submission'=> 'boolean',
        ]);

        $data['instructor_id'] = $instructor->id;

        Assignment::create($data);

        return redirect()->route('instructor.assignments.index')
            ->with('success', 'Assignment created.');
    }

    public function show(Assignment $assignment)
    {
        $this->authorizeOwner($assignment);

        $submissions = $assignment->submissions()
            ->with('student.user')
            ->latest('submitted_at')
            ->get()
            ->map(fn ($s) => [
                'id'           => $s->id,
                'student_name' => $s->student?->user?->name,
                'submitted_at' => $s->submitted_at?->toDateTimeString(),
                'is_late'      => $s->submitted_at && $assignment->due_date && $s->submitted_at->gt($assignment->due_date),
                'grade'        => $s->grade,
                'graded_at'    => $s->graded_at?->toDateTimeString(),
                'file_path'    => $s->file_path ? asset('storage/' . $s->file_path) : null,
            ]);

        return Inertia::render('Instructor/Assignments/Show', [
            'assignment'  => [
                'id'          => $assignment->id,
                'title'       => $assignment->title,
                'description' => $assignment->description,
                'due_date'    => $assignment->due_date?->toDateTimeString(),
                'max_marks'   => $assignment->max_marks,
                'course'      => $assignment->course?->title,
            ],
            'submissions' => $submissions,
        ]);
    }

    public function update(Request $request, Assignment $assignment)
    {
        $this->authorizeOwner($assignment);

        $data = $request->validate([
            'title'                => 'required|string|max:255',
            'description'          => 'nullable|string',
            'due_date'             => 'required|date',
            'max_marks'            => 'required|integer|min:1',
            'allow_late_submission'=> 'boolean',
        ]);

        $assignment->update($data);

        return back()->with('success', 'Assignment updated.');
    }

    public function destroy(Assignment $assignment)
    {
        $this->authorizeOwner($assignment);
        $assignment->delete();

        return redirect()->route('instructor.assignments.index')
            ->with('success', 'Assignment deleted.');
    }
}
