<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('returned_at')->nullable()->after('rejection_reason');
            $table->foreignId('returned_by')->nullable()->after('returned_at')->constrained('users')->nullOnDelete();
            $table->text('correction_remarks')->nullable()->after('returned_by');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('returned_by');
            $table->dropColumn(['returned_at', 'correction_remarks']);
        });
    }
};
