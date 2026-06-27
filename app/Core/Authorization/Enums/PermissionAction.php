<?php

namespace App\Core\Authorization\Enums;

enum PermissionAction: string
{
    case View = 'view';
    case Create = 'create';
    case Update = 'update';
    case Delete = 'delete';
    case Approve = 'approve';
    case Export = 'export';
    case Print = 'print';

    /**
     * @return list<self>
     */
    public static function all(): array
    {
        return [
            self::View,
            self::Create,
            self::Update,
            self::Delete,
            self::Approve,
            self::Export,
            self::Print,
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::View => 'View',
            self::Create => 'Create',
            self::Update => 'Update',
            self::Delete => 'Delete',
            self::Approve => 'Approve',
            self::Export => 'Export',
            self::Print => 'Print',
        };
    }
}
