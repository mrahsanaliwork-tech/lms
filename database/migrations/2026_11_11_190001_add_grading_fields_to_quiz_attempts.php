<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            // Store the question order shown to this student (for randomized quizzes)
            $table->json('question_order')->nullable()->after('answers');
            // Set when short-answer questions exist and need a human to grade them
            $table->boolean('needs_manual_grading')->default(false)->after('passed');
            // Store total possible marks so score percentage is self-contained
            $table->unsignedSmallInteger('total_marks')->default(0)->after('score');
        });
    }

    public function down(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropColumn(['question_order', 'needs_manual_grading', 'total_marks']);
        });
    }
};
