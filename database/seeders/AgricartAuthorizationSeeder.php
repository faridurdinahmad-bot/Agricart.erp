<?php

namespace Database\Seeders;

use App\Core\Authorization\Enums\UserRegistrationSource;
use App\Core\Authorization\Enums\UserStatus;
use App\Core\Authorization\PermissionGate;
use App\Core\Authorization\RoleManager;
use App\Models\User;
use Illuminate\Database\Seeder;

class AgricartAuthorizationSeeder extends Seeder
{
    public function run(): void
    {
        PermissionGate::syncDefinitions();

        $superAdminRole = RoleManager::ensureSuperAdminRole();

        User::query()->updateOrCreate(
            ['email' => 'admin@agricart.test'],
            [
                'staff_no' => 'STF-1',
                'name' => 'System Administrator',
                'name_urdu' => 'سسٹم ایڈمنسٹریٹر',
                'password' => 'password',
                'status' => UserStatus::Active,
                'role_id' => $superAdminRole->id,
                'join_date' => now()->toDateString(),
                'approved_at' => now(),
                'registration_source' => UserRegistrationSource::Admin,
            ],
        );
    }
}
