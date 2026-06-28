<?php

namespace App\Core\Filament\Layout;

use Filament\Navigation\NavigationGroup;
use Livewire\Component;
use Livewire\Livewire;

class CurrentPageLayout
{
    public static function breadcrumbs(): array
    {
        $shared = view()->getShared();

        if (array_key_exists('agricartLayoutBreadcrumbs', $shared)) {
            return $shared['agricartLayoutBreadcrumbs'];
        }

        if (! filament()->hasBreadcrumbs()) {
            return [];
        }

        $page = self::page();

        if (! $page || ! method_exists($page, 'getBreadcrumbs')) {
            return [];
        }

        return $page->getBreadcrumbs();
    }

    /**
     * @return array<int, NavigationGroup>
     */
    public static function subNavigation(): array
    {
        $shared = view()->getShared();

        if (array_key_exists('agricartLayoutSubNavigation', $shared)) {
            return $shared['agricartLayoutSubNavigation'];
        }

        $page = self::page();

        if (! $page || ! method_exists($page, 'getCachedSubNavigation')) {
            return [];
        }

        return $page->getCachedSubNavigation();
    }

    protected static function page(): ?Component
    {
        $livewire = Livewire::current();

        return $livewire instanceof Component ? $livewire : null;
    }
}
