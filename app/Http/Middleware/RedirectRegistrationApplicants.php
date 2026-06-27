<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectRegistrationApplicants
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user instanceof User && ! $user->canAccessPanel(Filament::getCurrentOrDefaultPanel())) {
            if ($user->requiresRegistrationStatusPage()) {
                return redirect()->route('registration.status');
            }

            abort(403);
        }

        return $next($request);
    }
}
