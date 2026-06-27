<?php

namespace App\Core\Staff\Concerns;

use App\Core\Authorization\UserProvisioner;
use App\Core\Staff\StaffFormValidator;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\WithFileUploads;

trait InteractsWithStaffForm
{
    use WithFileUploads;

    /** @var array<string, mixed> */
    public array $staffForm = [];

    public $profilePhoto = null;

    public $cnicFront = null;

    public $cnicBack = null;

    public ?string $existingProfilePhotoUrl = null;

    public ?string $existingCnicFrontUrl = null;

    public ?string $existingCnicBackUrl = null;

    public function addPhoneRow(): void
    {
        $this->staffForm['phones'][] = [
            'mobile' => '',
            'contact_person' => '',
            'is_emergency' => false,
            'is_default' => false,
        ];
    }

    public function removePhoneRow(int $index): void
    {
        if (count($this->staffForm['phones']) <= 1) {
            return;
        }

        unset($this->staffForm['phones'][$index]);
        $this->staffForm['phones'] = array_values($this->staffForm['phones']);
    }

    public function addBankRow(): void
    {
        $this->staffForm['bank_accounts'][] = [
            'bank_name' => '',
            'account_title' => '',
            'account_number' => '',
            'iban' => '',
        ];
    }

    public function removeBankRow(int $index): void
    {
        if (count($this->staffForm['bank_accounts']) <= 1) {
            return;
        }

        unset($this->staffForm['bank_accounts'][$index]);
        $this->staffForm['bank_accounts'] = array_values($this->staffForm['bank_accounts']);
    }

    protected function resetStaffForm(bool $includeStaffMeta = true): void
    {
        $this->staffForm = [
            ...UserProvisioner::emptyStaffForm(),
            'emergency_phone_index' => 0,
            'default_phone_index' => 0,
        ];

        if (! $includeStaffMeta) {
            unset($this->staffForm['staff_no'], $this->staffForm['join_date']);
        }

        $this->resetStaffFiles();
    }

    protected function loadStaffFormFromUser(User $user): void
    {
        $this->staffForm = UserProvisioner::staffFormFromUser($user);
        $this->existingProfilePhotoUrl = $user->profilePhotoUrl();
        $this->existingCnicFrontUrl = $user->cnicFrontUrl();
        $this->existingCnicBackUrl = $user->cnicBackUrl();
        $this->resetStaffFiles();
    }

    protected function resetStaffFiles(): void
    {
        $this->profilePhoto = null;
        $this->cnicFront = null;
        $this->cnicBack = null;
        $this->resetValidation([
            'staffForm.name',
            'staffForm.name_urdu',
            'staffForm.email',
            'staffForm.password',
            'staffForm.password_confirmation',
            'staffForm.phones',
            'staffForm.bank_accounts',
            'profilePhoto',
            'cnicFront',
            'cnicBack',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateStaffForm(): array
    {
        $this->validate(
            StaffFormValidator::rules(),
            StaffFormValidator::messages(),
            StaffFormValidator::attributes(),
        );

        return $this->prepareStaffPayload();
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateStaffFormForUpdate(User $user): array
    {
        $requireFiles = ! $user->profile_photo_path || ! $user->cnic_front_path || ! $user->cnic_back_path;

        $this->validate(
            StaffFormValidator::rulesForUpdate($user->id, $requireFiles),
            StaffFormValidator::messages(),
            StaffFormValidator::attributes(),
        );

        return $this->prepareStaffPayload();
    }

    /**
     * @return array<string, mixed>
     */
    protected function prepareStaffPayload(): array
    {
        $phones = collect($this->staffForm['phones'] ?? [])
            ->map(function (array $phone, int $index): array {
                return [
                    ...$phone,
                    'is_emergency' => (int) ($this->staffForm['emergency_phone_index'] ?? 0) === $index,
                    'is_default' => (int) ($this->staffForm['default_phone_index'] ?? 0) === $index,
                ];
            })
            ->all();

        return [
            'name' => trim((string) ($this->staffForm['name'] ?? '')),
            'name_urdu' => trim((string) ($this->staffForm['name_urdu'] ?? '')),
            'email' => trim((string) ($this->staffForm['email'] ?? '')),
            'password' => (string) ($this->staffForm['password'] ?? ''),
            'password_confirmation' => (string) ($this->staffForm['password_confirmation'] ?? ''),
            'phones' => $phones,
            'bank_accounts' => $this->staffForm['bank_accounts'] ?? [],
            'profile_photo' => $this->profilePhoto,
            'cnic_front' => $this->cnicFront,
            'cnic_back' => $this->cnicBack,
        ];
    }
}
