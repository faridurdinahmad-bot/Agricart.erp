<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('profile_photo_path')->nullable()->after('bank_accounts');
            $table->string('cnic_front_path')->nullable()->after('profile_photo_path');
            $table->string('cnic_back_path')->nullable()->after('cnic_front_path');
            $table->timestamp('rejected_at')->nullable()->after('approved_by');
            $table->foreignId('rejected_by')->nullable()->after('rejected_at')->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable()->after('rejected_by');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('rejected_by');
            $table->dropColumn([
                'profile_photo_path',
                'cnic_front_path',
                'cnic_back_path',
                'rejected_at',
                'rejection_reason',
            ]);
        });
    }
};
