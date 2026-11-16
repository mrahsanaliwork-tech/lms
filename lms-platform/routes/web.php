<?php

use App\Http\Controllers\Admin\CourseController as AdminCourseController;
use App\Http\Controllers\Instructor\CourseController as InstructorCourseController;
use App\Http\Controllers\Admin\CourseModuleController as AdminCourseModuleController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\LessonController as AdminLessonController;
use App\Http\Controllers\Admin\QuizController as AdminQuizController;
use App\Http\Controllers\Admin\StudentController as AdminStudentController;
use App\Http\Controllers\Student\QuizAttemptController;
use App\Http\Controllers\Instructor\AssignmentController as InstructorAssignmentController;
use App\Http\Controllers\Instructor\AssignmentSubmissionController as InstructorSubmissionController;
use App\Http\Controllers\Student\AssignmentController as StudentAssignmentController;
use App\Http\Controllers\Student\AssignmentSubmissionController as StudentSubmissionController;
use App\Http\Controllers\Student\LessonProgressController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\MyCoursesController;
use App\Http\Controllers\Student\CoursePlayerController;
use App\Http\Controllers\AiController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\AutomationWorkflowController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\CertificateController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ── Public ────────────────────────────────────────────────────────────────────

Route::get('/', fn () => Inertia::render('Home'))->name('home');

// ── Stripe Webhook (no auth — Stripe calls this directly) ─────────────────────
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])
    ->name('stripe.webhook')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// ── Certificate Verification (public, no auth) ───────────────────────────────
Route::get('/certificates/verify/{certNumber}', [CertificateController::class, 'verify'])
    ->name('certificates.verify');

// ── Auth (guests only) ────────────────────────────────────────────────────────

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:login')->name('login.store');
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// ── Profile (any authenticated user) ─────────────────────────────────────────

Route::middleware('auth')->prefix('profile')->name('profile.')->group(function () {
    Route::get('/', [ProfileController::class, 'edit'])->name('edit');
    Route::patch('/', [ProfileController::class, 'update'])->name('update');
    Route::patch('/password', [ProfileController::class, 'updatePassword'])->name('password');
    Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
});

// ── Notifications (all authenticated users) ──────────────────────────────────

Route::middleware('auth')->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/',          [NotificationController::class, 'index'])->name('index');
    Route::post('/{id}/read',[NotificationController::class, 'markRead'])->name('read');
    Route::post('/read-all', [NotificationController::class, 'markAllRead'])->name('read-all');
    Route::delete('/{id}',   [NotificationController::class, 'destroy'])->name('destroy');
});

// ── AI endpoints (all authenticated users) ───────────────────────────────────

Route::middleware(['auth', 'throttle:ai'])->prefix('ai')->name('ai.')->group(function () {
    Route::get('/tutor',                [AiController::class, 'tutorPage'])->name('tutor');
    Route::post('/chat',                [AiController::class, 'chat'])->name('chat');
    Route::post('/generate-quiz',       [AiController::class, 'generateQuiz'])->name('quiz');
    Route::post('/generate-description',[AiController::class, 'generateDescription'])->name('description');
    Route::post('/generate-summary',    [AiController::class, 'generateSummary'])->name('summary');
    Route::post('/analyze-student',     [AiController::class, 'analyzeStudent'])->name('analyze');
});

// ── Admin & SuperAdmin ────────────────────────────────────────────────────────

Route::middleware(['auth', 'role:SuperAdmin,Admin', 'log.admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');

        // Students
        Route::get('/students/export', [AdminStudentController::class, 'export'])->name('students.export');
        Route::post('/students/import', [AdminStudentController::class, 'bulkImport'])->name('students.import');
        Route::resource('/students', AdminStudentController::class)->names('students');

        // Courses — specific action routes before resource to avoid conflict
        Route::post('/courses/{course}/publish',   [AdminCourseController::class, 'publish'])->name('courses.publish');
        Route::post('/courses/{course}/unpublish', [AdminCourseController::class, 'unpublish'])->name('courses.unpublish');
        Route::post('/courses/{course}/archive',   [AdminCourseController::class, 'archive'])->name('courses.archive');
        Route::post('/courses/{course}/approve',   [AdminCourseController::class, 'approve'])->name('courses.approve');
        Route::post('/courses/{course}/reject',    [AdminCourseController::class, 'reject'])->name('courses.reject');
        Route::resource('/courses', AdminCourseController::class)->names('courses');

        // Modules & Lessons
        Route::post('/courses/{course}/modules',           [AdminCourseModuleController::class, 'store'])->name('modules.store');
        Route::post('/courses/{course}/modules/reorder',   [AdminCourseModuleController::class, 'reorder'])->name('modules.reorder');
        Route::patch('/modules/{module}',                  [AdminCourseModuleController::class, 'update'])->name('modules.update');
        Route::delete('/modules/{module}',                 [AdminCourseModuleController::class, 'destroy'])->name('modules.destroy');
        Route::post('/modules/{module}/lessons/reorder',   [AdminLessonController::class, 'reorder'])->name('lessons.reorder');
        Route::post('/lessons',                            [AdminLessonController::class, 'store'])->name('lessons.store');
        Route::patch('/lessons/{lesson}',                  [AdminLessonController::class, 'update'])->name('lessons.update');
        Route::delete('/lessons/{lesson}',                 [AdminLessonController::class, 'destroy'])->name('lessons.destroy');

        // Quizzes
        Route::post('/quizzes/{quiz}/publish',   [AdminQuizController::class, 'publish'])->name('quizzes.publish');
        Route::post('/quizzes/{quiz}/unpublish', [AdminQuizController::class, 'unpublish'])->name('quizzes.unpublish');
        Route::resource('/quizzes', AdminQuizController::class)->names('quizzes');

        // Payments & Coupons
        Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments.index');
        Route::resource('/coupons', CouponController::class)->names('coupons')->except(['create','edit','show']);

        // Automation Workflows
        Route::get('/automation',                     [AutomationWorkflowController::class, 'index'])->name('automation.index');
        Route::get('/automation/create',              [AutomationWorkflowController::class, 'create'])->name('automation.create');
        Route::post('/automation',                    [AutomationWorkflowController::class, 'store'])->name('automation.store');
        Route::get('/automation/{automation}/edit',   [AutomationWorkflowController::class, 'edit'])->name('automation.edit');
        Route::patch('/automation/{automation}',      [AutomationWorkflowController::class, 'update'])->name('automation.update');
        Route::post('/automation/{automation}/toggle',[AutomationWorkflowController::class, 'toggle'])->name('automation.toggle');
        Route::delete('/automation/{automation}',     [AutomationWorkflowController::class, 'destroy'])->name('automation.destroy');

        Route::get('/users', function () {
            $users = \App\Models\User::with('roles')
                ->latest()
                ->paginate(20)
                ->through(fn ($u) => [
                    'id'         => $u->id,
                    'name'       => $u->name,
                    'email'      => $u->email,
                    'roles'      => $u->roles->pluck('name'),
                    'status'     => $u->status ?? 'active',
                    'created_at' => $u->created_at->format('M d, Y'),
                ]);
            return \Inertia\Inertia::render('Admin/Users/Index', compact('users'));
        })->name('users.index');
        Route::post('/users/{user}/assign-role', function (\App\Models\User $user, \Illuminate\Http\Request $req) {
            $role = $req->validate(['role' => 'required|string'])['role'];
            $user->syncRoles([$role]);
            return back()->with('success', "Role updated to {$role}.");
        })->name('users.assign-role');
        Route::get('/organizations', [\App\Http\Controllers\Admin\OrganizationController::class, 'index'])->name('organizations.index');
        Route::post('/organizations', [\App\Http\Controllers\Admin\OrganizationController::class, 'store'])->name('organizations.store');
        Route::patch('/organizations/{organization}', [\App\Http\Controllers\Admin\OrganizationController::class, 'update'])->name('organizations.update');
        Route::delete('/organizations/{organization}', [\App\Http\Controllers\Admin\OrganizationController::class, 'destroy'])->name('organizations.destroy');
        // Reports
        Route::get('/reports', fn () => Inertia::render('Admin/Reports/Index'))->name('reports.index');
        Route::get('/reports/student-performance', [ReportsController::class, 'studentPerformance'])->name('reports.students');
        Route::get('/reports/revenue',             [ReportsController::class, 'revenueReport'])->name('reports.revenue');
        Route::get('/reports/course-analytics',    [ReportsController::class, 'courseAnalytics'])->name('reports.courses');
        Route::get('/reports/quiz',                [ReportsController::class, 'quizReport'])->name('reports.quiz');
        Route::get('/reports/ai-usage',            [ReportsController::class, 'aiUsageReport'])->name('reports.ai');
        Route::get('/settings', fn () => Inertia::render('Admin/Settings/Index'))->name('settings.index');
    });

// ── Instructor & Teaching Assistant ──────────────────────────────────────────

Route::middleware(['auth', 'role:Instructor,TeachingAssistant'])
    ->prefix('instructor')
    ->name('instructor.')
    ->group(function () {
        Route::get('/dashboard', function () {
            $user       = auth()->user();
            $instructor = $user->instructor;
            $courses    = \App\Models\Course::where('instructor_id', optional($instructor)->id)
                ->withCount(['enrollments', 'modules'])
                ->latest()->get();
            $totalStudents   = \App\Models\Enrollment::whereIn('course_id', $courses->pluck('id'))->distinct('student_id')->count();
            $pendingGrades   = \App\Models\AssignmentSubmission::whereHas('assignment', fn($q) => $q->whereIn('course_id', $courses->pluck('id')))->whereNull('grade')->count();
            $recentEnrollments = \App\Models\Enrollment::with(['student.user', 'course'])
                ->whereIn('course_id', $courses->pluck('id'))
                ->latest('enrolled_at')->limit(5)->get()
                ->map(fn($e) => [
                    'student' => $e->student->user->name ?? '—',
                    'course'  => $e->course->title ?? '—',
                    'date'    => $e->enrolled_at ? \Carbon\Carbon::parse($e->enrolled_at)->diffForHumans() : '—',
                ]);
            return Inertia::render('Instructor/Dashboard', [
                'stats' => [
                    'totalCourses'   => $courses->count(),
                    'totalStudents'  => $totalStudents,
                    'pendingGrades'  => $pendingGrades,
                    'publishedCount' => $courses->where('status', 'published')->count(),
                ],
                'courses'           => $courses->take(5)->map(fn($c) => [
                    'id'          => $c->id,
                    'title'       => $c->title,
                    'status'      => $c->status,
                    'enrollments' => $c->enrollments_count,
                    'lessons'     => $c->modules_count,
                ]),
                'recentEnrollments' => $recentEnrollments,
            ]);
        })->name('dashboard');
        Route::resource('/courses', InstructorCourseController::class)->names('courses');

        // Instructor reuses admin module/lesson controllers (ownership checked in controller)
        Route::post('/courses/{course}/modules',           [\App\Http\Controllers\Admin\CourseModuleController::class, 'store'])->name('modules.store');
        Route::post('/courses/{course}/modules/reorder',   [\App\Http\Controllers\Admin\CourseModuleController::class, 'reorder'])->name('modules.reorder');
        Route::patch('/modules/{module}',                  [\App\Http\Controllers\Admin\CourseModuleController::class, 'update'])->name('modules.update');
        Route::delete('/modules/{module}',                 [\App\Http\Controllers\Admin\CourseModuleController::class, 'destroy'])->name('modules.destroy');
        Route::post('/modules/{module}/lessons/reorder',   [\App\Http\Controllers\Admin\LessonController::class, 'reorder'])->name('lessons.reorder');
        Route::post('/lessons',                            [\App\Http\Controllers\Admin\LessonController::class, 'store'])->name('lessons.store');
        Route::patch('/lessons/{lesson}',                  [\App\Http\Controllers\Admin\LessonController::class, 'update'])->name('lessons.update');
        Route::delete('/lessons/{lesson}',                 [\App\Http\Controllers\Admin\LessonController::class, 'destroy'])->name('lessons.destroy');
        // Assignments
        Route::resource('/assignments', InstructorAssignmentController::class)
            ->names('assignments')
            ->except(['create', 'edit']);
        Route::get('/assignments/{assignment}/submissions/{submission}/grade',
            [InstructorSubmissionController::class, 'grade'])->name('submissions.grade');
        Route::post('/assignments/{assignment}/submissions/{submission}/grade',
            [InstructorSubmissionController::class, 'saveGrade'])->name('submissions.saveGrade');

        Route::get('/quizzes', fn () => Inertia::render('Instructor/Quizzes/Index'))->name('quizzes.index');
        Route::get('/attendance', fn () => Inertia::render('Instructor/Attendance/Index'))->name('attendance.index');
    });

// ── Organization Manager ──────────────────────────────────────────────────────

Route::middleware(['auth', 'role:OrganizationManager'])
    ->prefix('org')
    ->name('org.')
    ->group(function () {
        Route::get('/dashboard', fn () => Inertia::render('Org/Dashboard'))->name('dashboard');
        Route::get('/students', fn () => Inertia::render('Org/Students/Index'))->name('students.index');
        Route::get('/reports', fn () => Inertia::render('Org/Reports/Index'))->name('reports.index');
    });

// ── Student ───────────────────────────────────────────────────────────────────

Route::middleware(['auth', 'role:Student'])
    ->prefix('student')
    ->name('student.')
    ->group(function () {
        Route::get('/dashboard', StudentDashboardController::class)->name('dashboard');
        Route::get('/courses', [MyCoursesController::class, 'index'])->name('courses.index');
        Route::get('/courses/{course}/learn', [CoursePlayerController::class, 'show'])->name('courses.learn');
        Route::get('/courses/{course}/learn/{lesson}', [CoursePlayerController::class, 'show'])->name('courses.learn.lesson');
        Route::get('/certificates',                        [CertificateController::class, 'index'])->name('certificates.index');
        Route::get('/certificates/{certificate}/download', [CertificateController::class, 'download'])->name('certificates.download');
        Route::get('/progress', function () {
            $student     = auth()->user()->student;
            $enrollments = \App\Models\Enrollment::with(['course.instructor.user'])
                ->where('student_id', $student->id)
                ->orderByDesc('enrolled_at')
                ->get();
            return \Inertia\Inertia::render('Student/Progress/Index', compact('enrollments'));
        })->name('progress.index');

        // Assignments
        Route::get('/assignments',              [StudentAssignmentController::class, 'index'])->name('assignments.index');
        Route::get('/assignments/{assignment}', [StudentAssignmentController::class, 'show'])->name('assignments.show');
        Route::post('/assignments/{assignment}/submit', [StudentSubmissionController::class, 'store'])->name('assignments.submit');

        // Payments
        Route::get('/checkout/{course}',              [PaymentController::class, 'checkout'])->name('checkout');
        Route::post('/checkout/{course}/coupon',      [PaymentController::class, 'applyCoupon'])->name('checkout.coupon');
        Route::post('/checkout/{course}/session',     [PaymentController::class, 'createSession'])->name('checkout.session');
        Route::get('/payment/success',                [PaymentController::class, 'success'])->name('payment.success');
        Route::get('/payment/cancel/{course}',        [PaymentController::class, 'cancel'])->name('payment.cancel');
        Route::get('/payment-history',                [PaymentController::class, 'history'])->name('payment.history');

        // Lesson progress
        Route::post('/lessons/{lesson}/complete', [LessonProgressController::class, 'complete'])->name('lessons.complete');

        // Quiz attempts
        Route::get('/quizzes/{quiz}/start',         [QuizAttemptController::class, 'start'])->name('quizzes.start');
        Route::post('/attempts/{attempt}/submit',    [QuizAttemptController::class, 'submit'])->name('quizzes.submit');
        Route::get('/attempts/{attempt}/results',    [QuizAttemptController::class, 'results'])->name('quizzes.results');
        Route::get('/quizzes/{quizId}/latest',       [QuizAttemptController::class, 'latestResults'])->name('quizzes.results.latest');
    });
