<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Quiz;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QuizController extends Controller
{
    public function index(Request $request): Response
    {
        $quizzes = Quiz::with(['course'])
            ->withCount(['questions', 'attempts'])
            ->when($request->search, fn ($q, $s) => $q->where('title', 'like', "%{$s}%"))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->course, fn ($q, $c) => $q->where('course_id', $c))
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn ($q) => [
                'id'               => $q->id,
                'title'            => $q->title,
                'course'           => $q->course?->title,
                'course_id'        => $q->course_id,
                'time_limit'       => $q->time_limit,
                'passing_marks'    => $q->passing_marks,
                'attempts_allowed' => $q->attempts_allowed,
                'is_randomized'    => $q->is_randomized,
                'status'           => $q->status,
                'questions_count'  => $q->questions_count,
                'attempts_count'   => $q->attempts_count,
                'created_at'       => $q->created_at->format('M d, Y'),
            ]);

        return Inertia::render('Admin/Quizzes/Index', [
            'quizzes' => $quizzes,
            'courses' => Course::published()->get(['id', 'title']),
            'filters' => $request->only('search', 'status', 'course'),
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Admin/Quizzes/Create', [
            'courses'    => Course::published()->get(['id', 'title']),
            'course_id'  => $request->integer('course_id') ?: null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'course_id'        => ['required', 'exists:courses,id'],
            'lesson_id'        => ['nullable', 'exists:lessons,id'],
            'title'            => ['required', 'string', 'max:255'],
            'time_limit'       => ['nullable', 'integer', 'min:1', 'max:300'],
            'passing_marks'    => ['required', 'integer', 'min:1', 'max:100'],
            'attempts_allowed' => ['required', 'integer', 'min:0'],
            'is_randomized'    => ['boolean'],
            'status'           => ['required', 'in:draft,published,archived'],
            'questions'        => ['required', 'array', 'min:1'],
            'questions.*.question'      => ['required', 'string'],
            'questions.*.type'          => ['required', 'in:mcq,truefalse,short'],
            'questions.*.options'       => ['nullable', 'array'],
            'questions.*.correct_answer'=> ['required', 'string'],
            'questions.*.marks'         => ['required', 'integer', 'min:1'],
            'questions.*.explanation'   => ['nullable', 'string'],
        ]);

        $quiz = Quiz::create([
            'course_id'        => $data['course_id'],
            'lesson_id'        => $data['lesson_id'] ?? null,
            'title'            => $data['title'],
            'time_limit'       => $data['time_limit'] ?? null,
            'passing_marks'    => $data['passing_marks'],
            'attempts_allowed' => $data['attempts_allowed'],
            'is_randomized'    => $request->boolean('is_randomized'),
            'status'           => $data['status'],
        ]);

        foreach ($data['questions'] as $q) {
            $quiz->questions()->create($q);
        }

        return redirect()->route('admin.quizzes.show', $quiz)
            ->with('success', 'Quiz created.');
    }

    public function show(Quiz $quiz): Response
    {
        $quiz->load(['course', 'questions']);

        $stats = [
            'attempts'     => $quiz->attempts()->count(),
            'completed'    => $quiz->attempts()->completed()->count(),
            'passed'       => $quiz->attempts()->passed()->count(),
            'avg_score'    => round($quiz->attempts()->whereNotNull('completed_at')->avg('score') ?? 0),
        ];

        return Inertia::render('Admin/Quizzes/Show', [
            'quiz'      => [
                'id'               => $quiz->id,
                'title'            => $quiz->title,
                'course'           => $quiz->course?->title,
                'course_id'        => $quiz->course_id,
                'time_limit'       => $quiz->time_limit,
                'passing_marks'    => $quiz->passing_marks,
                'attempts_allowed' => $quiz->attempts_allowed,
                'is_randomized'    => $quiz->is_randomized,
                'status'           => $quiz->status,
            ],
            'questions' => $quiz->questions->map(fn ($q) => [
                'id'             => $q->id,
                'question'       => $q->question,
                'type'           => $q->type,
                'options'        => $q->options,
                'correct_answer' => $q->correct_answer,
                'marks'          => $q->marks,
                'explanation'    => $q->explanation,
            ]),
            'stats' => $stats,
        ]);
    }

    public function edit(Quiz $quiz): Response
    {
        $quiz->load('questions');

        return Inertia::render('Admin/Quizzes/Edit', [
            'quiz'      => [
                'id'               => $quiz->id,
                'title'            => $quiz->title,
                'course_id'        => $quiz->course_id,
                'lesson_id'        => $quiz->lesson_id,
                'time_limit'       => $quiz->time_limit,
                'passing_marks'    => $quiz->passing_marks,
                'attempts_allowed' => $quiz->attempts_allowed,
                'is_randomized'    => $quiz->is_randomized,
                'status'           => $quiz->status,
            ],
            'questions' => $quiz->questions->map(fn ($q) => [
                'id'             => $q->id,
                'question'       => $q->question,
                'type'           => $q->type,
                'options'        => $q->options ?? [],
                'correct_answer' => $q->correct_answer,
                'marks'          => $q->marks,
                'explanation'    => $q->explanation ?? '',
            ]),
            'courses' => Course::published()->get(['id', 'title']),
        ]);
    }

    public function update(Request $request, Quiz $quiz): RedirectResponse
    {
        $data = $request->validate([
            'title'            => ['required', 'string', 'max:255'],
            'course_id'        => ['required', 'exists:courses,id'],
            'lesson_id'        => ['nullable', 'exists:lessons,id'],
            'time_limit'       => ['nullable', 'integer', 'min:1', 'max:300'],
            'passing_marks'    => ['required', 'integer', 'min:1', 'max:100'],
            'attempts_allowed' => ['required', 'integer', 'min:0'],
            'is_randomized'    => ['boolean'],
            'status'           => ['required', 'in:draft,published,archived'],
            'questions'        => ['required', 'array', 'min:1'],
            'questions.*.id'            => ['nullable', 'integer'],
            'questions.*.question'      => ['required', 'string'],
            'questions.*.type'          => ['required', 'in:mcq,truefalse,short'],
            'questions.*.options'       => ['nullable', 'array'],
            'questions.*.correct_answer'=> ['required', 'string'],
            'questions.*.marks'         => ['required', 'integer', 'min:1'],
            'questions.*.explanation'   => ['nullable', 'string'],
        ]);

        $quiz->update([
            'title'            => $data['title'],
            'course_id'        => $data['course_id'],
            'lesson_id'        => $data['lesson_id'] ?? null,
            'time_limit'       => $data['time_limit'] ?? null,
            'passing_marks'    => $data['passing_marks'],
            'attempts_allowed' => $data['attempts_allowed'],
            'is_randomized'    => $request->boolean('is_randomized'),
            'status'           => $data['status'],
        ]);

        // Sync questions: delete removed ones, upsert the rest
        $incomingIds = collect($data['questions'])->pluck('id')->filter()->all();
        $quiz->questions()->whereNotIn('id', $incomingIds)->delete();

        foreach ($data['questions'] as $q) {
            $fields = collect($q)->except('id')->all();
            if (!empty($q['id'])) {
                $quiz->questions()->where('id', $q['id'])->update($fields);
            } else {
                $quiz->questions()->create($fields);
            }
        }

        return redirect()->route('admin.quizzes.show', $quiz)
            ->with('success', 'Quiz updated.');
    }

    public function destroy(Quiz $quiz): RedirectResponse
    {
        $quiz->delete();
        return redirect()->route('admin.quizzes.index')->with('success', 'Quiz deleted.');
    }

    public function publish(Quiz $quiz): RedirectResponse
    {
        $quiz->update(['status' => 'published']);
        return back()->with('success', 'Quiz published.');
    }

    public function unpublish(Quiz $quiz): RedirectResponse
    {
        $quiz->update(['status' => 'draft']);
        return back()->with('success', 'Quiz unpublished.');
    }
}
