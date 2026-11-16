<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('plan_name', 100)->index();
            $table->decimal('price', 10, 2);
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('expires_at')->nullable()->index();
            $table->enum('status', ['active', 'cancelled', 'expired', 'past_due'])->default('active')->index();
            $table->string('stripe_subscription_id')->nullable()->unique()->index();
            $table->timestamps();

            $table->index(['student_id', 'status']);
            $table->index(['student_id', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
