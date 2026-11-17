<?php

namespace App\Services;

use App\Models\AutomationWorkflow;
use App\Models\AutomationLog;
use App\Models\Enrollment;
use App\Notifications\WelcomeNotification;
use App\Notifications\EnrollmentConfirmation;
use App\Notifications\PaymentConfirmation;
use App\Notifications\CertificateIssued;
use App\Notifications\QuizCompleted as QuizCompletedNotification;
use App\Notifications\AssignmentGraded;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

class AutomationProcessor
{
    public function processWorkflow(string $trigger, array $context): void
    {
        $workflows = AutomationWorkflow::where('trigger_type', $trigger)
            ->where('is_active', true)
            ->get();

        foreach ($workflows as $workflow) {
            $this->runWorkflow($workflow, $context);
        }
    }

    private function runWorkflow(AutomationWorkflow $workflow, array $context): void
    {
        $startedAt = now();
        $status    = 'success';
        $error     = null;

        try {
            $conditions = $workflow->conditions ?? [];

            if (!empty($conditions) && !$this->evaluateConditions($conditions, $context)) {
                $status = 'skipped';
                $this->log($workflow, $context, $status, 'Conditions not met');
                return;
            }

            foreach ($workflow->actions ?? [] as $action) {
                $this->executeAction($action, $context);
            }
        } catch (Throwable $e) {
            $status = 'failed';
            $error  = $e->getMessage();
            Log::error("Automation workflow {$workflow->id} failed: {$e->getMessage()}");
        }

        $this->log($workflow, $context, $status, $error);
    }

    public function evaluateConditions(array $conditions, array $context): bool
    {
        $operator = $conditions['operator'] ?? 'and';
        $rules    = $conditions['rules'] ?? [];

        if (empty($rules)) {
            return true;
        }

        $results = array_map(fn($rule) => $this->evaluateRule($rule, $context), $rules);

        return $operator === 'or'
            ? in_array(true, $results, true)
            : !in_array(false, $results, true);
    }

    private function evaluateRule(array $rule, array $context): bool
    {
        $field    = $rule['field'] ?? '';
        $op       = $rule['operator'] ?? 'equals';
        $expected = $rule['value'] ?? null;
        $actual   = data_get($context, $field);

        return match ($op) {
            'equals'            => $actual == $expected,
            'not_equals'        => $actual != $expected,
            'contains'          => str_contains((string) $actual, (string) $expected),
            'not_contains'      => !str_contains((string) $actual, (string) $expected),
            'greater_than'      => is_numeric($actual) && $actual > $expected,
            'less_than'         => is_numeric($actual) && $actual < $expected,
            'greater_than_eq'   => is_numeric($actual) && $actual >= $expected,
            'less_than_eq'      => is_numeric($actual) && $actual <= $expected,
            'is_empty'          => empty($actual),
            'is_not_empty'      => !empty($actual),
            default             => false,
        };
    }

    public function executeAction(array $action, array $context): void
    {
        $type = $action['type'] ?? '';
        $user = $context['user'] ?? null;

        match ($type) {
            'send_welcome_email'      => $this->actionSendWelcomeEmail($user),
            'send_enrollment_email'   => $this->actionSendEnrollmentEmail($context),
            'send_payment_email'      => $this->actionSendPaymentEmail($context),
            'send_certificate_email'  => $this->actionSendCertificateEmail($context),
            'send_notification'       => $this->actionSendNotification($action, $context),
            'enroll_student'          => $this->actionEnrollStudent($action, $context),
            'notify_instructor'       => $this->actionNotifyInstructor($action, $context),
            'add_role'                => $this->actionAddRole($action, $context),
            'wait'                    => null, // handled by scheduled job
            default                   => Log::warning("Unknown automation action: {$type}"),
        };
    }

    // ── Action implementations ─────────────────────────────────────────────────

    private function actionSendWelcomeEmail($user): void
    {
        if ($user) {
            $user->notify(new WelcomeNotification());
        }
    }

    private function actionSendEnrollmentEmail(array $context): void
    {
        $user   = $context['user'] ?? null;
        $course = $context['course'] ?? null;
        if ($user && $course) {
            $user->notify(new EnrollmentConfirmation($course));
        }
    }

    private function actionSendPaymentEmail(array $context): void
    {
        $user    = $context['user'] ?? null;
        $payment = $context['payment'] ?? null;
        if ($user && $payment) {
            $user->notify(new PaymentConfirmation($payment));
        }
    }

    private function actionSendCertificateEmail(array $context): void
    {
        $user   = $context['user'] ?? null;
        $course = $context['course'] ?? null;
        if ($user && $course) {
            $certUrl = "/student/certificates/{$course->id}";
            $user->notify(new CertificateIssued($course->title, $course->id, $certUrl));
        }
    }

    private function actionSendNotification(array $action, array $context): void
    {
        $user    = $context['user'] ?? null;
        $title   = $action['title'] ?? 'Notification';
        $message = $action['message'] ?? '';
        if ($user) {
            \Illuminate\Support\Facades\DB::table('notifications')->insert([
                'id'              => \Illuminate\Support\Str::uuid(),
                'type'            => 'App\\Notifications\\Generic',
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id'   => $user->id,
                'data'            => json_encode([
                    'type'    => 'automation',
                    'title'   => $title,
                    'message' => $message,
                    'url'     => $action['url'] ?? null,
                    'icon'    => 'bell',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function actionEnrollStudent(array $action, array $context): void
    {
        $user     = $context['user'] ?? null;
        $courseId = $action['course_id'] ?? ($context['course']?->id ?? null);
        if ($user && $courseId) {
            Enrollment::firstOrCreate([
                'student_id' => $user->student?->id,
                'course_id'  => $courseId,
            ], ['enrolled_at' => now(), 'status' => 'active']);
        }
    }

    private function actionNotifyInstructor(array $action, array $context): void
    {
        $course  = $context['course'] ?? null;
        $message = $action['message'] ?? 'A student action requires your attention.';
        if ($course && $course->instructor?->user) {
            \Illuminate\Support\Facades\DB::table('notifications')->insert([
                'id'              => \Illuminate\Support\Str::uuid(),
                'type'            => 'App\\Notifications\\InstructorAlert',
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id'   => $course->instructor->user->id,
                'data'            => json_encode([
                    'type'    => 'instructor_alert',
                    'title'   => 'Course activity',
                    'message' => $message,
                    'url'     => "/instructor/courses/{$course->id}",
                    'icon'    => 'bell',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function actionAddRole(array $action, array $context): void
    {
        $user = $context['user'] ?? null;
        $role = $action['role'] ?? null;
        if ($user && $role) {
            $user->assignRole($role);
        }
    }

    // ── Logging ────────────────────────────────────────────────────────────────

    private function log(AutomationWorkflow $workflow, array $context, string $status, ?string $error = null): void
    {
        try {
            AutomationLog::create([
                'workflow_id' => $workflow->id,
                'trigger'     => $workflow->trigger_type,
                'context'     => array_map(fn($v) => is_object($v) ? get_class($v) . '#' . ($v->id ?? '') : $v, $context),
                'status'      => $status,
                'error'       => $error,
            ]);
        } catch (Throwable $e) {
            Log::error("Failed to log automation: {$e->getMessage()}");
        }
    }
}
