<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ai_conversations', function (Blueprint $table) {
            $table->string('model')->default('gpt-4o-mini')->after('tokens_used');
            $table->decimal('cost_estimate', 10, 6)->default(0)->after('model');
            $table->string('type')->default('chat')->after('cost_estimate'); // chat|quiz|description|summary|analysis
        });
    }

    public function down(): void
    {
        Schema::table('ai_conversations', function (Blueprint $table) {
            $table->dropColumn(['model', 'cost_estimate', 'type']);
        });
    }
};
