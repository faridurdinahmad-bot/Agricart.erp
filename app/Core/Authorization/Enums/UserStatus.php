<?php

namespace App\Core\Authorization\Enums;

enum UserStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Inactive = 'inactive';
    case Rejected = 'rejected';
    case ReturnedForCorrection = 'returned_for_correction';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Active => 'Active',
            self::Inactive => 'Inactive',
            self::Rejected => 'Rejected',
            self::ReturnedForCorrection => 'Returned for Correction',
        };
    }
}
