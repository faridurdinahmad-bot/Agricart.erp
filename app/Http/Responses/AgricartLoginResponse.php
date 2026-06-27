<?php

namespace App\Http\Responses;

use App\Models\User;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class AgricartLoginResponse implements LoginResponse
{
    public function toResponse($request): RedirectResponse | Redirector
    {
        $user = auth()->user();
        $panel = Filament::getCurrentOrDefaultPanel();

        if ($user instanceof User && $user->canAccessPanel($panel)) {
            return redirect()->intended(Filament::getUrl());
        }

        return redirect()->route('registration.status');
    }
}
