<?php

namespace App\Modules\Catalog\Enums;

enum BrandLifecycleStatus: string
{
    case Active = 'active';
    case PendingDeletion = 'pending_deletion';
    case Deleted = 'deleted';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::PendingDeletion => 'Pending Deletion',
            self::Deleted => 'Deleted',
        };
    }
}
