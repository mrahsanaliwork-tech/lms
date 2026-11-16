<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique()->index();
            $table->longText('description')->nullable();
            $table->foreignId('instructor_id')->constrained('instructors')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->string('thumbnail')->nullable();
            $table->decimal('price', 10, 2)->default(0.00);
            $table->boolean('is_free')->default(false)->index();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft')->index();
            $table->enum('level', ['beginner', 'intermediate', 'advanced'])->default('beginner')->index();
            $table->string('language', 10)->default('en')->index();
            $table->json('prerequisites')->nullable();
            $table->unsignedInteger('duration')->nullable()->comment('Total duration in minutes');
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['instructor_id', 'status']);
            $table->index(['category_id', 'status']);
            $table->index(['status', 'published_at']);
            $table->index(['is_free', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
