<div class="agricart-topbar-tools">
    <x-filament::dropdown placement="bottom-end" teleport>
        <x-slot name="trigger">
            <button
                type="button"
                class="agricart-topbar-tool-btn"
                aria-label="{{ __('Language') }}"
            >
                {{ \Filament\Support\generate_icon_html(\Filament\Support\Icons\Heroicon::OutlinedLanguage, attributes: new \Illuminate\View\ComponentAttributeBag(['class' => 'agricart-topbar-tool-btn__icon'])) }}
                <span class="agricart-topbar-tool-btn__label">EN</span>
            </button>
        </x-slot>

        <x-filament::dropdown.list>
            <x-filament::dropdown.list.item tag="button" icon="heroicon-o-check">
                English
            </x-filament::dropdown.list.item>
            <x-filament::dropdown.list.item tag="button">
                Urdu
            </x-filament::dropdown.list.item>
        </x-filament::dropdown.list>
    </x-filament::dropdown>

    <div class="agricart-topbar-theme-switcher">
        <x-filament-panels::theme-switcher />
    </div>

    <button
        type="button"
        class="agricart-topbar-tool-btn"
        aria-label="{{ __('Notifications') }}"
    >
        {{ \Filament\Support\generate_icon_html(\Filament\Support\Icons\Heroicon::OutlinedBell, attributes: new \Illuminate\View\ComponentAttributeBag(['class' => 'agricart-topbar-tool-btn__icon'])) }}
    </button>
</div>
