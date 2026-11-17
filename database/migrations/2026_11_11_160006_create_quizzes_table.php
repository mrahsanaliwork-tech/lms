<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->unsignedSmallInteger('time_limit')->nullable()->comment('Time limit in minutes');
            $table->unsignedSmallInteger('passing_marks')->default(50)->comment('Passing percentage');
            $table->unsignedTinyInteger('attempts_allowed')->default(1)->comment('0 = unlimited');
            $table->boolean('is_randomized')->default(false);
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft')->index();
            $table->timestamps();

            $table->index(['course_id', 'status']);
            $table->index(['lesson_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
