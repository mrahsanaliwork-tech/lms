<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('automation_workflows', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('trigger_type', 100)->index();
            $table->json('conditions')->nullable();
            $table->json('actions');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('last_run_at')->nullable()->index();
            $table->timestamps();

            $table->index(['trigger_type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_workflows');
    }
};
