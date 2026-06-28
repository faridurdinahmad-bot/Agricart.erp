<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('catalog_categories', function (Blueprint $table) {
            $table->timestamp('content_reviewed_at')->nullable()->after('ai_content_status');
            $table->foreignId('content_reviewed_by')->nullable()->after('content_reviewed_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('catalog_categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('content_reviewed_by');
            $table->dropColumn('content_reviewed_at');
        });
    }
};
