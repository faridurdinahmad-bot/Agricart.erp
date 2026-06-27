<?php

namespace App\Core\Filament\Pages;

use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;

abstract class BaseModulePage extends Page
{
    protected static bool $shouldRegisterNavigation = true;

    public function getBreadcrumbs(): array
    {
        $cluster = static::getCluster();

        return $cluster::unshiftClusterBreadcrumbs([
            static::getUrl() => static::getTitle(),
        ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        View::make('filament.components.coming-soon'),
                    ])
                    ->extraAttributes([
                        'class' => 'agricart-coming-soon-section',
                    ]),
            ]);
    }
}
