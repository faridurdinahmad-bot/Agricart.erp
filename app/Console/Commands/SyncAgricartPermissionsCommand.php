<?php

namespace App\Console\Commands;

use App\Core\Authorization\PermissionGate;
use App\Core\Authorization\RoleManager;
use Illuminate\Console\Command;

class SyncAgricartPermissionsCommand extends Command
{
    protected $signature = 'agricart:sync-permissions';

    protected $description = 'Force-sync permission definitions and ensure the Super Admin role exists.';

    public function handle(): int
    {
        PermissionGate::syncDefinitions();
        RoleManager::ensureSuperAdminRole();

        $this->info('Permissions synced and Super Admin role ensured.');

        return self::SUCCESS;
    }
}
