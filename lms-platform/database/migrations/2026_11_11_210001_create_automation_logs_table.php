<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('automation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained('automation_workflows')->cascadeOnDelete();
            $table->string('trigger', 100);
            $table->json('context')->nullable();
            $table->enum('status', ['success', 'failed', 'skipped'])->default('success');
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index(['workflow_id', 'created_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_logs');
    }
};
