<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->text('question');
            $table->enum('type', ['mcq', 'truefalse', 'short'])->default('mcq')->index();
            $table->json('options')->nullable()->comment('Array of answer options for mcq/truefalse');
            $table->text('correct_answer');
            $table->unsignedSmallInteger('marks')->default(1);
            $table->text('explanation')->nullable();
            $table->timestamps();

            $table->index(['quiz_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_questions');
    }
};
