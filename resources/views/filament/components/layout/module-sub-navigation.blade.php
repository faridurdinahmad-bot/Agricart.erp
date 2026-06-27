@php
    $subNavigation = \App\Core\Filament\Layout\CurrentPageLayout::subNavigation();
@endphp

@if (filled($subNavigation))
    <div class="agricart-module-subnav">
        <x-filament-panels::page.sub-navigation.tabs :navigation="$subNavigation" />
    </div>
@endif
