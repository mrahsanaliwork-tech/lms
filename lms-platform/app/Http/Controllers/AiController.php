<?php

namespace App\Http\Controllers;

use App\Models\AiConversation;
use App\Models\Course;
use App\Models\Student;
use App\Services\AIService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use RuntimeException;

class AiController extends Controller
{
    public function __construct(private readonly AIService $ai) {}

    // ── Page ───────────────────────────────────────────────────────

    public function tutorPage()
    {
        $courses = Course::where('status', 'published')->get(['id', 'title']);

        return Inertia::render('Student/AiTutor', compact('courses'));
    }

    // ── Chat (AI Tutor — maintains history) ────────────────────────

    public function chat(Request $request): JsonResponse
    {
        $data = $request->validate([
            'messages'  => 'required|array|min:1',
            'messages.*.role'    => 'required|in:user,assistant',
            'messages.*.content' => 'required|string|max:4000',
            'course_id' => 'nullable|integer|exists:courses,id',
        ]);

        $context = '';
        if (!empty($data['course_id'])) {
            $course  = Course::find($data['course_id']);
            $context = "Course: {$course->title}\nDescription: {$course->description}";
        }

        return $this->handleAI('chat', function () use ($data, $context) {
            return $this->ai->chat($data['messages'], $context);
        }, $data['messages'], $data['course_id'] ?? null);
    }

    // ── Quiz generation ────────────────────────────────────────────

    public function generateQuiz(Request $request): JsonResponse
    {
        $data = $request->validate([
            'content'    => 'required|string|min:50|max:8000',
            'count'      => 'integer|min:1|max:20',
            'difficulty' => 'in:easy,medium,hard',
        ]);

        return $this->handleAI('quiz', function () use ($data) {
            return $this->ai->generateQuiz(
                $data['content'],
                $data['count'] ?? 5,
                $data['difficulty'] ?? 'medium'
            );
        });
    }

    // ── Course description generation ─────────────────────────────

    public function generateDescription(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'topic' => 'required|string|max:500',
        ]);

        return $this->handleAI('description', function () use ($data) {
            return $this->ai->generateCourseDescription($data['title'], $data['topic']);
        });
    }

    // ── Student performance analysis ───────────────────────────────

    public function analyzeStudent(Request $request): JsonResponse
    {
        $data = $request->validate([
            'student_id' => 'nullable|integer|exists:students,id',
        ]);

        $studentId = $data['student_id'] ?? null;

        // If caller is a student, only allow own analysis
        if (Auth::user()->hasRole('Student')) {
            $student   = Student::where('user_id', Auth::id())->firstOrFail();
            $studentId = $student->id;
        }

        abort_if(!$studentId, 422, 'student_id is required.');

        $student = Student::with(['quizAttempts', 'enrollments'])->findOrFail($studentId);

        $performanceData = [
            'quiz_scores'       => $student->quizAttempts
                ->whereNotNull('score')
                ->pluck('score')
                ->values()
                ->all(),
            'completion_rate'   => $student->enrollments->avg('progress') ?? 0,
            'total_enrollments' => $student->enrollments->count(),
        ];

        return $this->handleAI('analysis', function () use ($performanceData) {
            return $this->ai->analyzeStudentPerformance($performanceData);
        }, [], null, $studentId);
    }

    // ── Lesson summary ─────────────────────────────────────────────

    public function generateSummary(Request $request): JsonResponse
    {
        $data = $request->validate([
            'content' => 'required|string|min:50|max:8000',
        ]);

        return $this->handleAI('summary', function () use ($data) {
            return $this->ai->generateLessonSummary($data['content']);
        });
    }

    // ── Shared error-handling wrapper ──────────────────────────────

    private function handleAI(
        string $type,
        callable $fn,
        array $messages = [],
        ?int $courseId = null,
        ?int $studentId = null,
    ): JsonResponse {
        try {
            $result = $fn();

            $this->log($type, $messages, $courseId, $studentId, $result);

            return response()->json(['result' => $result]);

        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['error' => 'An unexpected error occurred. Please try again.'], 500);
        }
    }

    private function log(string $type, array $messages, ?int $courseId, ?int $studentId, mixed $result): void
    {
        try {
            $user    = Auth::user();
            $student = $studentId
                ? Student::find($studentId)
                : ($user ? Student::where('user_id', $user->id)->first() : null);

            // Add the AI response as the last message for chat logs
            $allMessages = $messages;
            if ($type === 'chat' && is_string($result)) {
                $allMessages[] = ['role' => 'assistant', 'content' => $result];
            }

            AiConversation::create([
                'student_id'    => $student?->id,
                'course_id'     => $courseId,
                'messages'      => $allMessages,
                'tokens_used'   => 0, // Updated with real usage when using streaming
                'model'         => $this->ai->getModel(),
                'cost_estimate' => 0,
                'type'          => $type,
            ]);
        } catch (\Throwable) {
            // Logging must never break the response
        }
    }
}
