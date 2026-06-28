<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_brands', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name_en');
            $table->string('name_ur');
            $table->string('logo_path')->nullable();
            $table->text('short_note')->nullable();

            $table->text('short_description_en')->nullable();
            $table->text('short_description_ur')->nullable();
            $table->text('long_description_en')->nullable();
            $table->text('long_description_ur')->nullable();
            $table->text('brand_overview_en')->nullable();
            $table->text('brand_overview_ur')->nullable();

            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('seo_keywords')->nullable();

            $table->string('country')->nullable();
            $table->string('website')->nullable();

            $table->boolean('is_active')->default(true);
            $table->string('lifecycle_status')->default('active');
            $table->string('ai_content_status')->default('ai_pending');
            $table->timestamp('content_reviewed_at')->nullable();
            $table->foreignId('content_reviewed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('last_ai_generated_at')->nullable();
            $table->string('last_ai_model')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
            $table->index('lifecycle_status');
            $table->index('ai_content_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_brands');
    }
};
