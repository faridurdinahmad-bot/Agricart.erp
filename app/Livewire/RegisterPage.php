<?php

namespace App\Livewire;

use App\Core\Authorization\Enums\UserRegistrationSource;
use App\Core\Authorization\Enums\UserStatus;
use App\Core\Authorization\UserProvisioner;
use App\Core\Staff\Concerns\InteractsWithStaffForm;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.register')]
#[Title('Register — Agricart')]
class RegisterPage extends Component
{
    use InteractsWithStaffForm;

    public bool $submitted = false;

    public bool $isEditing = false;

    public bool $resubmitted = false;

    public function mount(): void
    {
        $user = auth()->user();

        if ($user instanceof User) {
            if ($user->canAccessPanel(Filament::getPanel('admin'))) {
                $this->redirect(Filament::getUrl());

                return;
            }

            if ($user->status === UserStatus::ReturnedForCorrection && request()->boolean('edit')) {
                $this->isEditing = true;
                $this->loadStaffFormFromUser($user);

                return;
            }

            $this->redirect(route('registration.status'));

            return;
        }

        $this->resetStaffForm(includeStaffMeta: false);
    }

    public function register(): void
    {
        try {
            if ($this->isEditing) {
                $this->resubmitApplication();

                return;
            }

            $payload = $this->validateStaffForm();

            UserProvisioner::createPending($payload, UserRegistrationSource::Registration);

            $this->submitted = true;
            $this->resetStaffForm(includeStaffMeta: false);
        } catch (ValidationException $exception) {
            throw $exception;
        }
    }

    protected function resubmitApplication(): void
    {
        /** @var User $user */
        $user = auth()->user();

        if (! $user->isReturnedForCorrection()) {
            $this->redirect(route('registration.status'));

            return;
        }

        $payload = $this->validateStaffFormForUpdate($user);

        UserProvisioner::resubmitApplication($user, $payload);

        $this->resubmitted = true;
        $this->submitted = true;
        $this->isEditing = false;
    }

    public function render(): View
    {
        return view('livewire.register-page');
    }
}
