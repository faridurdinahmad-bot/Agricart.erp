<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('catalog_brand_deletion_requests', function (Blueprint $table) {
            $table->boolean('was_active_before_request')->default(true)->after('reason');
        });
    }

    public function down(): void
    {
        Schema::table('catalog_brand_deletion_requests', function (Blueprint $table) {
            $table->dropColumn('was_active_before_request');
        });
    }
};
