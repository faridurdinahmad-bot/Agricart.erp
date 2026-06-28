<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('catalog_categories')->nullOnDelete();
            $table->string('code')->unique();
            $table->string('name_en');
            $table->string('name_ur');
            $table->string('image_path')->nullable();
            $table->string('hs_code')->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->text('short_description_en')->nullable();
            $table->text('short_description_ur')->nullable();
            $table->text('long_description_en')->nullable();
            $table->text('long_description_ur')->nullable();
            $table->text('usage_en')->nullable();
            $table->text('usage_ur')->nullable();
            $table->text('benefits_en')->nullable();
            $table->text('benefits_ur')->nullable();
            $table->text('warnings_en')->nullable();
            $table->text('warnings_ur')->nullable();

            $table->string('seo_title')->nullable();
            $table->string('seo_focus_keyword_en')->nullable();
            $table->string('seo_focus_keyword_ur')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->string('url_slug')->nullable();
            $table->string('canonical_url')->nullable();
            $table->string('meta_robots')->default('index, follow');
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();

            $table->string('synonyms_en')->nullable();
            $table->string('synonyms_ur')->nullable();
            $table->string('alternate_spellings')->nullable();
            $table->string('search_aliases')->nullable();
            $table->text('ai_prompt_override')->nullable();
            $table->string('internal_tags')->nullable();

            $table->string('google_category')->nullable();
            $table->string('facebook_category')->nullable();

            $table->timestamp('last_ai_generated_at')->nullable();
            $table->string('last_ai_model')->nullable();

            $table->timestamps();

            $table->unique(['parent_id', 'name_en']);
            $table->index(['parent_id', 'display_order']);
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_categories');
    }
};
