<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->json('answers')->nullable()->comment('Map of question_id => submitted answer');
            $table->unsignedSmallInteger('score')->default(0);
            $table->boolean('passed')->default(false)->index();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('completed_at')->nullable()->index();
            $table->timestamps();

            $table->index(['student_id', 'quiz_id']);
            $table->index(['student_id', 'passed']);
            $table->index(['quiz_id', 'passed']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
    }
};
