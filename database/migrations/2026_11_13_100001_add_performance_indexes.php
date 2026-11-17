<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // enrollments — most queried join table
        Schema::table('enrollments', function (Blueprint $table) {
            if (!$this->hasIndex('enrollments', 'enrollments_student_id_course_id_index')) {
                $table->index(['student_id', 'course_id']);
            }
            if (!$this->hasIndex('enrollments', 'enrollments_status_index')) {
                $table->index('status');
            }
            if (!$this->hasIndex('enrollments', 'enrollments_progress_index')) {
                $table->index('progress');
            }
        });

        // payments
        Schema::table('payments', function (Blueprint $table) {
            if (!$this->hasIndex('payments', 'payments_student_id_status_index')) {
                $table->index(['student_id', 'status']);
            }
            if (!$this->hasIndex('payments', 'payments_paid_at_index')) {
                $table->index('paid_at');
            }
        });

        // lesson_progress — drives course progress calculation
        Schema::table('lesson_progress', function (Blueprint $table) {
            if (!$this->hasIndex('lesson_progress', 'lesson_progress_student_id_lesson_id_index')) {
                $table->index(['student_id', 'lesson_id']);
            }
            if (!$this->hasIndex('lesson_progress', 'lesson_progress_completed_at_index')) {
                $table->index('completed_at');
            }
        });

        // quiz_attempts
        Schema::table('quiz_attempts', function (Blueprint $table) {
            if (!$this->hasIndex('quiz_attempts', 'quiz_attempts_student_id_quiz_id_index')) {
                $table->index(['student_id', 'quiz_id']);
            }
            if (!$this->hasIndex('quiz_attempts', 'quiz_attempts_completed_at_index')) {
                $table->index('completed_at');
            }
        });

        // notifications — Inertia reads unread frequently
        Schema::table('notifications', function (Blueprint $table) {
            if (!$this->hasIndex('notifications', 'notifications_notifiable_read_at_index')) {
                $table->index(['notifiable_id', 'notifiable_type', 'read_at']);
            }
        });

        // activity_logs
        Schema::table('activity_logs', function (Blueprint $table) {
            if (!$this->hasIndex('activity_logs', 'activity_logs_user_id_index')) {
                $table->index('user_id');
            }
            if (!$this->hasIndex('activity_logs', 'activity_logs_created_at_index')) {
                $table->index('created_at');
            }
        });

        // courses
        Schema::table('courses', function (Blueprint $table) {
            if (!$this->hasIndex('courses', 'courses_status_approval_status_index')) {
                $table->index(['status', 'approval_status']);
            }
        });

        // assignment_submissions
        if (Schema::hasTable('assignment_submissions')) {
            Schema::table('assignment_submissions', function (Blueprint $table) {
                if (!$this->hasIndex('assignment_submissions', 'assignment_submissions_student_id_assignment_id_index')) {
                    $table->index(['student_id', 'assignment_id']);
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('enrollments',   fn (Blueprint $t) => $t->dropIndex(['student_id', 'course_id']));
        Schema::table('payments',      fn (Blueprint $t) => $t->dropIndex(['student_id', 'status']));
        Schema::table('lesson_progress', fn (Blueprint $t) => $t->dropIndex(['student_id', 'lesson_id']));
        Schema::table('quiz_attempts', fn (Blueprint $t) => $t->dropIndex(['student_id', 'quiz_id']));
    }

    private function hasIndex(string $table, string $index): bool
    {
        $indexes = \Illuminate\Support\Facades\DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = '{$index}'");
        return !empty($indexes);
    }
};
