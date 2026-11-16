<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('instructor_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->longText('description')->nullable();
            $table->timestamp('due_date')->nullable()->index();
            $table->unsignedSmallInteger('max_marks')->default(100);
            $table->boolean('allow_late_submission')->default(false);
            $table->timestamps();

            $table->index(['course_id', 'due_date']);
            $table->index(['instructor_id', 'course_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
