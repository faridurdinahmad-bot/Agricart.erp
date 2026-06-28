<?php

namespace App\Core\Ai\Enums;

enum AiTargetModule: string
{
    case Settings = 'settings';
    case Catalog = 'catalog';
    case Products = 'products';
    case Brands = 'brands';
    case Attributes = 'attributes';
    case Inventory = 'inventory';
    case System = 'system';

    public function label(): string
    {
        return match ($this) {
            self::Settings => 'Settings',
            self::Catalog => 'Catalog',
            self::Products => 'Products',
            self::Brands => 'Brands',
            self::Attributes => 'Attributes',
            self::Inventory => 'Inventory',
            self::System => 'System',
        };
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $module): array => [
                'value' => $module->value,
                'label' => $module->label(),
            ],
            self::cases(),
        );
    }
}
