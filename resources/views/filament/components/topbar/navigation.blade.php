@php
    $breadcrumbs = \App\Core\Filament\Layout\CurrentPageLayout::breadcrumbs();
@endphp

@if (filled($breadcrumbs))
    <div class="agricart-topbar-breadcrumbs">
        <x-filament::breadcrumbs :breadcrumbs="$breadcrumbs" />
    </div>
@endif

<div class="agricart-topbar-search">
    @livewire(\Filament\Livewire\GlobalSearch::class)
</div>
