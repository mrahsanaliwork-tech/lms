<?php

namespace App\Providers;

use App\Events\AssignmentSubmitted;
use App\Events\CourseCompleted;
use App\Events\PaymentCompleted;
use App\Events\QuizCompleted;
use App\Events\StudentEnrolled;
use App\Events\StudentRegistered;
use App\Listeners\OnAssignmentSubmitted;
use App\Listeners\OnCourseCompleted;
use App\Listeners\OnPaymentCompleted;
use App\Listeners\OnQuizCompleted;
use App\Listeners\OnStudentEnrolled;
use App\Listeners\OnStudentRegistered;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Quiz;
use App\Models\Student;
use App\Policies\AssignmentPolicy;
use App\Policies\CoursePolicy;
use App\Policies\QuizPolicy;
use App\Policies\StudentPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // ── Event → Listener wiring ────────────────────────────────
        Event::listen(StudentRegistered::class,   OnStudentRegistered::class);
        Event::listen(StudentEnrolled::class,     OnStudentEnrolled::class);
        Event::listen(PaymentCompleted::class,    OnPaymentCompleted::class);
        Event::listen(CourseCompleted::class,     OnCourseCompleted::class);
        Event::listen(QuizCompleted::class,       OnQuizCompleted::class);
        Event::listen(AssignmentSubmitted::class, OnAssignmentSubmitted::class);

        // ── Policies ───────────────────────────────────────────────
        Gate::policy(Course::class,     CoursePolicy::class);
        Gate::policy(Quiz::class,       QuizPolicy::class);
        Gate::policy(Assignment::class, AssignmentPolicy::class);
        Gate::policy(Student::class,    StudentPolicy::class);

        // ── Horizon gate ──────────────────────────────────────────
        Gate::define('admin-horizon', fn ($user) => $user->hasAnyRole(['SuperAdmin', 'Admin']));

        // ── Rate limiters ──────────────────────────────────────────
        RateLimiter::for('ai', function (Request $request) {
            return Limit::perMinute(20)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });
    }
}
