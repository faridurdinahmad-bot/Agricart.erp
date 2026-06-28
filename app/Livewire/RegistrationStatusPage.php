<?php

namespace App\Livewire;

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.register')]
#[Title('Registration Status — Agricart')]
class RegistrationStatusPage extends Component
{
    public function mount(): void
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            $this->redirect(route('filament.admin.auth.login'));

            return;
        }

        if ($user->canAccessPanel(Filament::getPanel('admin'))) {
            $this->redirect(Filament::getUrl());
        }
    }

    public function logout(): void
    {
        auth()->logout();
        session()->invalidate();
        session()->regenerateToken();

        $this->redirect(route('filament.admin.auth.login'));
    }

    public function render(): View
    {
        /** @var User $user */
        $user = auth()->user();

        return view('livewire.registration-status-page', [
            'user' => $user,
            'status' => $user->status,
        ]);
    }
}
