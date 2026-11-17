<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiConversation;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use League\Csv\Writer;

class ReportsController extends Controller
{
    // ── Shared filter helpers ──────────────────────────────────────

    private function dateRange(Request $request): array
    {
        return [
            $request->input('from', now()->subDays(30)->toDateString()),
            $request->input('to',   now()->toDateString()),
        ];
    }

    // ── Student Performance ────────────────────────────────────────

    public function studentPerformance(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $query = Student::with('user:id,name,email')
            ->withCount([
                'enrollments',
                'enrollments as completed_enrollments_count' => fn($q) => $q->where('status', 'completed'),
                'quizAttempts as quiz_attempts_count',
                'assignmentSubmissions as submissions_count',
            ])
            ->withAvg('enrollments as avg_progress', 'progress');

        if ($request->filled('course_id')) {
            $courseId = $request->input('course_id');
            $query->whereHas('enrollments', fn($q) => $q->where('course_id', $courseId));
        }

        if ($request->filled('student_id')) {
            $query->where('id', $request->input('student_id'));
        }

        $students = $query->latest()->paginate(25)->withQueryString();

        $rows = $students->map(fn($s) => [
            'id'               => $s->id,
            'name'             => $s->user?->name ?? '—',
            'email'            => $s->user?->email ?? '—',
            'enrollments'      => $s->enrollments_count,
            'completed'        => $s->completed_enrollments_count,
            'avg_progress'     => round($s->avg_progress ?? 0),
            'quiz_attempts'    => $s->quiz_attempts_count,
            'submissions'      => $s->submissions_count,
        ]);

        if ($request->input('export') === 'csv') {
            return $this->exportCsv($rows->toArray(),
                ['Name', 'Email', 'Courses', 'Completed', 'Avg Progress %', 'Quiz Attempts', 'Submissions'],
                ['name','email','enrollments','completed','avg_progress','quiz_attempts','submissions'],
                'student-performance'
            );
        }

        if ($request->input('export') === 'pdf') {
            return $this->exportPdf('reports.student-performance',
                ['rows' => $rows, 'from' => $from, 'to' => $to],
                'student-performance'
            );
        }

        $courses  = Course::select('id', 'title')->orderBy('title')->get();
        $allStudents = Student::with('user:id,name')->get()->map(fn($s) => [
            'id' => $s->id, 'name' => $s->user?->name ?? '—',
        ]);

        return Inertia::render('Admin/Reports/StudentPerformance', [
            'rows'       => $rows,
            'pagination' => $students->toArray(),
            'filters'    => ['from' => $from, 'to' => $to,
                             'course_id' => $request->input('course_id'),
                             'student_id'=> $request->input('student_id')],
            'courses'    => $courses,
            'students'   => $allStudents,
        ]);
    }

    // ── Revenue Report ─────────────────────────────────────────────

    public function revenueReport(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $query = Payment::with('course:id,title', 'student.user:id,name')
            ->completed()
            ->whereBetween('paid_at', [$from, $to . ' 23:59:59']);

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->input('course_id'));
        }

        // Revenue by month (chart)
        $byMonth = Payment::completed()
            ->whereBetween('paid_at', [$from, $to . ' 23:59:59'])
            ->select(
                DB::raw("DATE_FORMAT(paid_at, '%Y-%m') as month"),
                DB::raw('SUM(amount) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $rows = $query->latest('paid_at')->paginate(25)->withQueryString();

        $tableRows = $rows->map(fn($p) => [
            'id'          => $p->id,
            'student'     => $p->student?->user?->name ?? '—',
            'course'      => $p->course?->title ?? '—',
            'amount'      => $p->amount,
            'currency'    => $p->currency,
            'discount'    => $p->discount_amount,
            'method'      => $p->payment_method,
            'paid_at'     => $p->paid_at?->format('M j, Y'),
        ]);

        if ($request->input('export') === 'csv') {
            return $this->exportCsv($tableRows->toArray(),
                ['Student', 'Course', 'Amount', 'Currency', 'Discount', 'Method', 'Paid At'],
                ['student','course','amount','currency','discount','method','paid_at'],
                'revenue-report'
            );
        }

        if ($request->input('export') === 'pdf') {
            return $this->exportPdf('reports.revenue',
                ['rows' => $tableRows, 'byMonth' => $byMonth, 'from' => $from, 'to' => $to],
                'revenue-report'
            );
        }

        $totals = [
            'revenue'       => $query->sum('amount'),
            'transactions'  => $query->count(),
            'avg_order'     => $query->count() > 0 ? round($query->sum('amount') / $query->count(), 2) : 0,
            'discounts'     => $query->sum('discount_amount'),
        ];

        $courses = Course::select('id', 'title')->orderBy('title')->get();

        return Inertia::render('Admin/Reports/Revenue', [
            'rows'       => $tableRows,
            'pagination' => $rows->toArray(),
            'byMonth'    => $byMonth,
            'totals'     => $totals,
            'filters'    => ['from' => $from, 'to' => $to, 'course_id' => $request->input('course_id')],
            'courses'    => $courses,
        ]);
    }

    // ── Course Analytics ──────────────────────────────────────────

    public function courseAnalytics(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        // Enrollment trend by month (chart)
        $enrollmentTrend = Enrollment::whereBetween('created_at', [$from, $to . ' 23:59:59'])
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('COUNT(*) as enrollments')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Per-course stats
        $courses = Course::with('instructor.user:id,name')
            ->withCount('enrollments')
            ->withCount(['enrollments as completed_count' => fn($q) => $q->where('status', 'completed')])
            ->withAvg('enrollments as avg_progress', 'progress')
            ->orderByDesc('enrollments_count')
            ->paginate(20)
            ->withQueryString();

        $rows = $courses->map(fn($c) => [
            'id'               => $c->id,
            'title'            => $c->title,
            'instructor'       => $c->instructor?->user?->name ?? '—',
            'enrollments'      => $c->enrollments_count,
            'completed'        => $c->completed_count,
            'completion_rate'  => $c->enrollments_count > 0
                ? round(($c->completed_count / $c->enrollments_count) * 100) : 0,
            'avg_progress'     => round($c->avg_progress ?? 0),
            'status'           => $c->status,
        ]);

        // Completion distribution for doughnut
        $completionDistribution = [
            ['label' => 'Not Started (0%)',   'value' => Enrollment::where('progress', 0)->count()],
            ['label' => 'In Progress (1-99%)', 'value' => Enrollment::whereBetween('progress', [1, 99])->count()],
            ['label' => 'Completed (100%)',    'value' => Enrollment::where('progress', 100)->count()],
        ];

        if ($request->input('export') === 'csv') {
            return $this->exportCsv($rows->toArray(),
                ['Course', 'Instructor', 'Enrollments', 'Completed', 'Completion Rate %', 'Avg Progress %'],
                ['title','instructor','enrollments','completed','completion_rate','avg_progress'],
                'course-analytics'
            );
        }

        if ($request->input('export') === 'pdf') {
            return $this->exportPdf('reports.course-analytics',
                ['rows' => $rows, 'from' => $from, 'to' => $to],
                'course-analytics'
            );
        }

        return Inertia::render('Admin/Reports/CourseAnalytics', [
            'rows'                   => $rows,
            'pagination'             => $courses->toArray(),
            'enrollmentTrend'        => $enrollmentTrend,
            'completionDistribution' => $completionDistribution,
            'filters'                => ['from' => $from, 'to' => $to],
        ]);
    }

    // ── Quiz Report ───────────────────────────────────────────────

    public function quizReport(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $quizzes = Quiz::with('course:id,title')
            ->withCount('attempts')
            ->withCount(['attempts as passed_count' => fn($q) => $q->where('passed', true)->whereNotNull('completed_at')])
            ->withAvg(['attempts as avg_score' => fn($q) => $q->whereNotNull('completed_at')], 'score')
            ->orderByDesc('attempts_count')
            ->paginate(20);

        $rows = $quizzes->map(fn($q) => [
            'id'          => $q->id,
            'title'       => $q->title,
            'course'      => $q->course?->title ?? '—',
            'attempts'    => $q->attempts_count,
            'passed'      => $q->passed_count,
            'pass_rate'   => $q->attempts_count > 0
                ? round(($q->passed_count / $q->attempts_count) * 100) : 0,
            'avg_score'   => round($q->avg_score ?? 0, 1),
            'passing_marks' => $q->passing_marks,
        ]);

        if ($request->input('export') === 'csv') {
            return $this->exportCsv($rows->toArray(),
                ['Quiz', 'Course', 'Attempts', 'Passed', 'Pass Rate %', 'Avg Score', 'Passing Marks'],
                ['title','course','attempts','passed','pass_rate','avg_score','passing_marks'],
                'quiz-report'
            );
        }

        return Inertia::render('Admin/Reports/QuizReport', [
            'rows'       => $rows,
            'pagination' => $quizzes->toArray(),
            'filters'    => ['from' => $from, 'to' => $to],
        ]);
    }

    // ── AI Usage Report ───────────────────────────────────────────

    public function aiUsageReport(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $byDay = AiConversation::whereBetween('created_at', [$from, $to . ' 23:59:59'])
            ->select(
                DB::raw("DATE(created_at) as day"),
                DB::raw('SUM(tokens_used) as tokens'),
                DB::raw('SUM(cost_estimate) as cost'),
                DB::raw('COUNT(*) as conversations')
            )
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $totals = AiConversation::whereBetween('created_at', [$from, $to . ' 23:59:59'])
            ->selectRaw('SUM(tokens_used) as tokens, SUM(cost_estimate) as cost, COUNT(*) as conversations')
            ->first();

        return Inertia::render('Admin/Reports/AiUsage', [
            'byDay'   => $byDay,
            'totals'  => $totals,
            'filters' => ['from' => $from, 'to' => $to],
        ]);
    }

    // ── Export helpers ─────────────────────────────────────────────

    private function exportCsv(array $rows, array $headers, array $keys, string $filename)
    {
        $csv = Writer::createFromString();
        $csv->insertOne($headers);
        foreach ($rows as $row) {
            $csv->insertOne(array_map(fn($k) => $row[$k] ?? '', $keys));
        }
        return response($csv->toString(), 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ]);
    }

    private function exportPdf(string $view, array $data, string $filename)
    {
        $pdf = Pdf::loadView($view, $data)->setPaper('a4', 'landscape');
        return $pdf->download("{$filename}.pdf");
    }
}
