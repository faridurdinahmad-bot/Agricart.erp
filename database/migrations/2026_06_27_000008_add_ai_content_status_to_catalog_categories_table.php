<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('catalog_categories', function (Blueprint $table) {
            $table->string('ai_content_status')->default('ai_pending')->after('is_active');
            $table->index('ai_content_status');
        });
    }

    public function down(): void
    {
        Schema::table('catalog_categories', function (Blueprint $table) {
            $table->dropIndex(['ai_content_status']);
            $table->dropColumn('ai_content_status');
        });
    }
};
