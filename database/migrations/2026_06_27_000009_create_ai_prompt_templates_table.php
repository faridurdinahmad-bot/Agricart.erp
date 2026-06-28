<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_prompt_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('target_module');
            $table->string('task_type');
            $table->text('system_prompt');
            $table->text('user_prompt_template');
            $table->string('output_format')->default('json');
            $table->decimal('temperature', 3, 2)->nullable();
            $table->unsignedInteger('max_output_tokens')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('available_variables')->nullable();
            $table->timestamps();

            $table->index(['task_type', 'target_module', 'is_active']);
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_prompt_templates');
    }
};
