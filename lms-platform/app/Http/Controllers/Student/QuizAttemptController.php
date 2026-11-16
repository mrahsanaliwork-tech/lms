<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class QuizAttemptController extends Controller
{
    private function student(): Student
    {
        return Student::where('user_id', Auth::id())->firstOrFail();
    }

    /**
     * Start or resume a quiz attempt.
     * Creates a new attempt record (enforces attempt limits) and shows the quiz UI.
     */
    public function start(Quiz $quiz): Response|RedirectResponse
    {
        $student = $this->student();

        // Check the quiz is published
        abort_if($quiz->status !== 'published', 403, 'This quiz is not available.');

        // Enforce attempt limit
        if ($quiz->attempts_allowed > 0) {
            $used = QuizAttempt::where('student_id', $student->id)
                ->where('quiz_id', $quiz->id)
                ->count();
            if ($used >= $quiz->attempts_allowed) {
                return redirect()->route('student.quizzes.results.latest', $quiz->id)
                    ->with('error', 'You have used all allowed attempts for this quiz.');
            }
        }

        // Build ordered question list
        $questions = $quiz->questions()->get();
        $ordered   = $quiz->is_randomized
            ? $questions->shuffle()
            : $questions->sortBy('id');

        $questionOrder = $ordered->pluck('id')->all();

        // Create attempt
        $attempt = QuizAttempt::create([
            'student_id'     => $student->id,
            'quiz_id'        => $quiz->id,
            'question_order' => $questionOrder,
            'started_at'     => now(),
        ]);

        // Strip correct_answer before sending to client
        $questionsForClient = $ordered->map(fn ($q) => [
            'id'       => $q->id,
            'question' => $q->question,
            'type'     => $q->type,
            'options'  => $q->options,
            'marks'    => $q->marks,
        ])->values()->all();

        return Inertia::render('Student/Quiz/Take', [
            'quiz' => [
                'id'            => $quiz->id,
                'title'         => $quiz->title,
                'time_limit'    => $quiz->time_limit,
                'passing_marks' => $quiz->passing_marks,
                'course'        => $quiz->course?->title,
            ],
            'attempt' => [
                'id'         => $attempt->id,
                'started_at' => $attempt->started_at->toISOString(),
            ],
            'questions' => $questionsForClient,
        ]);
    }

    /**
     * Submit answers, grade, store result.
     */
    public function submit(Request $request, QuizAttempt $attempt): RedirectResponse
    {
        $student = $this->student();
        abort_if($attempt->student_id !== $student->id, 403);
        abort_if($attempt->completed_at !== null, 409, 'Already submitted.');

        $request->validate([
            'answers'   => ['required', 'array'],
            'answers.*' => ['nullable', 'string', 'max:5000'],
        ]);

        $quiz      = $attempt->quiz()->with('questions')->first();
        $answers   = $request->answers; // ['question_id' => 'answer_string']
        $score     = 0;
        $total     = 0;
        $hasShort  = false;

        foreach ($quiz->questions as $question) {
            $total      += $question->marks;
            $submitted   = $answers[$question->id] ?? null;

            if ($question->type === 'short') {
                $hasShort = true;
                // Short answers need manual grading — mark as 0 for now
            } elseif ($submitted !== null) {
                $correct = trim(strtolower($question->correct_answer));
                $given   = trim(strtolower((string) $submitted));
                if ($given === $correct) {
                    $score += $question->marks;
                }
            }
        }

        $scorePercentage = $total > 0 ? round(($score / $total) * 100) : 0;
        $passed          = !$hasShort && $scorePercentage >= $quiz->passing_marks;

        $attempt->update([
            'answers'              => $answers,
            'score'                => $scorePercentage,
            'total_marks'          => $total,
            'passed'               => $passed,
            'needs_manual_grading' => $hasShort,
            'completed_at'         => now(),
        ]);

        return redirect()->route('student.quizzes.results', $attempt->id);
    }

    /**
     * Show results + answer review for a completed attempt.
     */
    public function results(QuizAttempt $attempt): Response
    {
        $student = $this->student();
        abort_if($attempt->student_id !== $student->id, 403);
        abort_if($attempt->completed_at === null, 404);

        $quiz      = $attempt->quiz()->with('questions')->first();
        $answers   = $attempt->answers ?? [];

        $reviewed = $quiz->questions->map(function ($q) use ($answers) {
            $submitted = $answers[$q->id] ?? null;
            $correct   = null;

            if ($q->type !== 'short') {
                $correct = strtolower($q->correct_answer) === strtolower((string) $submitted);
            }

            return [
                'id'             => $q->id,
                'question'       => $q->question,
                'type'           => $q->type,
                'options'        => $q->options,
                'correct_answer' => $q->correct_answer,
                'submitted'      => $submitted,
                'is_correct'     => $correct,
                'marks'          => $q->marks,
                'explanation'    => $q->explanation,
            ];
        });

        $totalAttempts = QuizAttempt::where('student_id', $student->id)
            ->where('quiz_id', $quiz->id)
            ->count();

        return Inertia::render('Student/Quiz/Results', [
            'quiz' => [
                'id'               => $quiz->id,
                'title'            => $quiz->title,
                'course'           => $quiz->course?->title,
                'passing_marks'    => $quiz->passing_marks,
                'attempts_allowed' => $quiz->attempts_allowed,
                'can_retake'       => $quiz->attempts_allowed === 0 || $totalAttempts < $quiz->attempts_allowed,
            ],
            'attempt' => [
                'id'                   => $attempt->id,
                'score'                => $attempt->score,
                'total_marks'          => $attempt->total_marks,
                'passed'               => $attempt->passed,
                'needs_manual_grading' => $attempt->needs_manual_grading,
                'started_at'           => $attempt->started_at->format('M d, Y H:i'),
                'completed_at'         => $attempt->completed_at->format('M d, Y H:i'),
                'duration_minutes'     => $attempt->started_at->diffInMinutes($attempt->completed_at),
            ],
            'questions'      => $reviewed,
            'total_attempts' => $totalAttempts,
        ]);
    }

    /**
     * Redirect to the latest attempt results.
     */
    public function latestResults(int $quizId): RedirectResponse
    {
        $student = $this->student();
        $latest  = QuizAttempt::where('student_id', $student->id)
            ->where('quiz_id', $quizId)
            ->whereNotNull('completed_at')
            ->latest()
            ->firstOrFail();

        return redirect()->route('student.quizzes.results', $latest->id);
    }
}
