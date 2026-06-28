<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_category_url_redirects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('catalog_categories')->cascadeOnDelete();
            $table->string('old_url', 500);
            $table->string('new_url', 500);
            $table->unsignedSmallInteger('redirect_status')->default(301);
            $table->timestamp('changed_at');
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['category_id', 'changed_at']);
            $table->index('old_url');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_category_url_redirects');
    }
};
