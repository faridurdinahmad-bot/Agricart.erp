<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('staff_no')->nullable()->unique()->after('id');
            $table->string('name_urdu')->nullable()->after('name');
            $table->string('status')->default('pending')->after('password');
            $table->foreignId('role_id')->nullable()->after('status')->constrained()->nullOnDelete();
            $table->date('join_date')->nullable()->after('role_id');
            $table->timestamp('approved_at')->nullable()->after('join_date');
            $table->foreignId('approved_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
            $table->string('registration_source')->default('admin')->after('approved_by');
            $table->json('phones')->nullable()->after('registration_source');
            $table->json('bank_accounts')->nullable()->after('phones');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by');
            $table->dropConstrainedForeignId('role_id');
            $table->dropColumn([
                'staff_no',
                'name_urdu',
                'status',
                'join_date',
                'approved_at',
                'registration_source',
                'phones',
                'bank_accounts',
            ]);
        });
    }
};
