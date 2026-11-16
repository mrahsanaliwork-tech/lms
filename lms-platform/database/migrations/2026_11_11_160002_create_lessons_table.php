<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('course_modules')->cascadeOnDelete();
            $table->string('title');
            $table->enum('type', ['video', 'pdf', 'text'])->default('video')->index();
            $table->string('content_url')->nullable();
            $table->unsignedInteger('duration')->nullable()->comment('Duration in seconds');
            $table->unsignedSmallInteger('order')->default(0);
            $table->boolean('is_free')->default(false)->index();
            $table->enum('status', ['active', 'inactive'])->default('active')->index();
            $table->timestamps();

            $table->index(['module_id', 'order']);
            $table->index(['module_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
