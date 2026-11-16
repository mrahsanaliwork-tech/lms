<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'stats'             => Cache::remember('dashboard.stats',          300, fn () => $this->stats()),
            'monthlyRevenue'    => Cache::remember('dashboard.monthlyRevenue', 300, fn () => $this->monthlyRevenue()),
            'enrollmentTrend'   => Cache::remember('dashboard.enrollmentTrend',300, fn () => $this->enrollmentTrend()),
            'recentEnrollments' => $this->recentEnrollments(),   // always fresh
            'recentActivity'    => $this->recentActivity(),      // always fresh
        ]);
    }

    private function stats(): array
    {
        $totalStudents  = Student::count();
        $activeStudents = Student::whereHas('user', fn ($q) =>
            $q->where('last_login_at', '>=', now()->subDays(30))
        )->count();

        $totalCourses      = Course::count();
        $totalEnrollments  = Enrollment::count();

        $totalRevenue   = Payment::where('status', 'completed')->sum('amount');
        $pendingRevenue = Payment::where('status', 'pending')->sum('amount');

        $completedEnrollments = Enrollment::where('status', 'completed')->count();
        $completionRate = $totalEnrollments > 0
            ? round(($completedEnrollments / $totalEnrollments) * 100, 1)
            : 0;

        // Compare to previous 30-day window for trend
        $newStudentsThisMonth = Student::where('created_at', '>=', now()->subDays(30))->count();
        $newStudentsLastMonth = Student::whereBetween('created_at', [
            now()->subDays(60), now()->subDays(30),
        ])->count();

        $studentTrend = $newStudentsLastMonth > 0
            ? round((($newStudentsThisMonth - $newStudentsLastMonth) / $newStudentsLastMonth) * 100, 1)
            : null;

        $newEnrollmentsThis = Enrollment::where('created_at', '>=', now()->subDays(30))->count();
        $newEnrollmentsLast = Enrollment::whereBetween('created_at', [
            now()->subDays(60), now()->subDays(30),
        ])->count();

        $enrollmentTrend = $newEnrollmentsLast > 0
            ? round((($newEnrollmentsThis - $newEnrollmentsLast) / $newEnrollmentsLast) * 100, 1)
            : null;

        return compact(
            'totalStudents', 'activeStudents', 'totalCourses',
            'totalEnrollments', 'totalRevenue', 'pendingRevenue',
            'completionRate', 'studentTrend', 'enrollmentTrend'
        );
    }

    private function monthlyRevenue(): array
    {
        $rows = Payment::where('status', 'completed')
            ->where('paid_at', '>=', now()->subMonths(6)->startOfMonth())
            ->select(
                DB::raw("DATE_FORMAT(paid_at, '%Y-%m') as month"),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Fill all 6 months even if no data
        $months = collect();
        for ($i = 5; $i >= 0; $i--) {
            $key = now()->subMonths($i)->format('Y-m');
            $months[$key] = 0;
        }

        foreach ($rows as $row) {
            $months[$row->month] = (float) $row->total;
        }

        return [
            'labels' => $months->keys()->map(fn ($m) => \Carbon\Carbon::parse($m)->format('M Y'))->values()->all(),
            'data'   => $months->values()->all(),
        ];
    }

    private function enrollmentTrend(): array
    {
        $rows = Enrollment::where('created_at', '>=', now()->subMonths(6)->startOfMonth())
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $months = collect();
        for ($i = 5; $i >= 0; $i--) {
            $key = now()->subMonths($i)->format('Y-m');
            $months[$key] = 0;
        }

        foreach ($rows as $row) {
            $months[$row->month] = (int) $row->total;
        }

        return [
            'labels' => $months->keys()->map(fn ($m) => \Carbon\Carbon::parse($m)->format('M Y'))->values()->all(),
            'data'   => $months->values()->all(),
        ];
    }

    private function recentEnrollments(): array
    {
        return Enrollment::with(['student.user', 'course'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn ($e) => [
                'id'          => $e->id,
                'student'     => $e->student?->user?->name ?? '—',
                'course'      => $e->course?->title ?? '—',
                'enrolled_at' => $e->enrolled_at?->diffForHumans(),
                'status'      => $e->status,
                'progress'    => $e->progress,
            ])
            ->all();
    }

    private function recentActivity(): array
    {
        return ActivityLog::with('user')
            ->latest()
            ->limit(15)
            ->get()
            ->map(fn ($log) => [
                'id'         => $log->id,
                'user'       => $log->user?->name ?? 'System',
                'action'     => $log->action,
                'model_type' => class_basename($log->model_type ?? ''),
                'ip_address' => $log->ip_address,
                'created_at' => $log->created_at->diffForHumans(),
            ])
            ->all();
    }
}
