<?php

namespace App\Core\Filament\Pages;

use App\Models\User;
use Filament\Clusters\Cluster;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;

abstract class BaseModulePage extends Page
{
    protected static bool $shouldRegisterNavigation = true;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return false;
        }

        $clusterClass = static::getCluster();

        if (! $clusterClass || ! is_subclass_of($clusterClass, Cluster::class)) {
            return false;
        }

        /** @var class-string<Cluster> $clusterClass */
        $moduleSlug = $clusterClass::getSlug();
        $pageSlug = static::getSlug();

        return $user->canViewPage($moduleSlug, $pageSlug);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::$shouldRegisterNavigation && static::canAccess();
    }

    public function rendering(): void
    {
        view()->share('agricartLayoutBreadcrumbs', $this->getBreadcrumbs());
        view()->share('agricartLayoutSubNavigation', $this->getCachedSubNavigation());
    }

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
