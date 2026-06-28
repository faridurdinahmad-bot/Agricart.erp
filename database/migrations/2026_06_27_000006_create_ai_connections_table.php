<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_connections', function (Blueprint $table) {
            $table->id();
            $table->string('provider');
            $table->text('api_key');
            $table->string('base_url');
            $table->string('model');
            $table->unsignedInteger('context_window')->default(128000);
            $table->unsignedInteger('max_output_tokens')->default(4096);
            $table->decimal('temperature', 3, 2)->default(0.70);
            $table->unsignedSmallInteger('timeout')->default(60);
            $table->unsignedTinyInteger('retry_count')->default(2);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamp('last_tested_at')->nullable();
            $table->string('last_test_status')->nullable();
            $table->unsignedInteger('last_test_response_time_ms')->nullable();
            $table->string('last_test_rate_limit_remaining')->nullable();
            $table->text('last_test_error')->nullable();
            $table->json('available_models')->nullable();
            $table->timestamps();

            $table->index(['provider', 'is_active']);
            $table->index('is_default');
            $table->index('last_test_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_connections');
    }
};
