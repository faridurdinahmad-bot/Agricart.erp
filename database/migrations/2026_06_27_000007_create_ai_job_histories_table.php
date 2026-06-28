<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_job_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('ai_connection_id')->nullable()->constrained('ai_connections')->nullOnDelete();
            $table->string('provider');
            $table->string('model');
            $table->string('task_type');
            $table->string('target_module');
            $table->string('target_type')->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->boolean('success')->default(false);
            $table->unsignedInteger('response_time_ms')->default(0);
            $table->unsignedInteger('tokens_input')->nullable();
            $table->unsignedInteger('tokens_output')->nullable();
            $table->unsignedInteger('tokens_total')->nullable();
            $table->decimal('estimated_cost', 12, 6)->nullable();
            $table->text('error_message')->nullable();
            $table->json('context_snapshot')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['task_type', 'created_at']);
            $table->index(['target_module', 'created_at']);
            $table->index(['success', 'created_at']);
            $table->index(['target_type', 'target_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_job_histories');
    }
};
