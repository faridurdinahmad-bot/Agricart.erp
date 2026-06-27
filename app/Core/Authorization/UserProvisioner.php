<?php

namespace App\Core\Authorization;

use App\Core\Authorization\Enums\UserRegistrationSource;
use App\Core\Authorization\Enums\UserStatus;
use App\Core\Staff\StaffFileStorage;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class UserProvisioner
{
    /**
     * @param  array<string, mixed>  $data  Pre-validated payload from StaffFormValidator
     */
    public static function createPending(array $data, UserRegistrationSource $source): User
    {
        $user = User::query()->create([
            'staff_no' => $source === UserRegistrationSource::Admin ? User::generateStaffNumber() : null,
            'name' => $data['name'],
            'name_urdu' => $data['name_urdu'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'status' => UserStatus::Pending,
            'role_id' => null,
            'join_date' => now()->toDateString(),
            'registration_source' => $source,
            'phones' => self::normalizePhones($data['phones'] ?? []),
            'bank_accounts' => self::normalizeBankAccounts($data['bank_accounts'] ?? []),
        ]);

        StaffFileStorage::storeForUser($user, [
            'profile_photo' => $data['profile_photo'] ?? null,
            'cnic_front' => $data['cnic_front'] ?? null,
            'cnic_back' => $data['cnic_back'] ?? null,
        ]);

        return $user->refresh();
    }

    /**
     * @param  array<string, mixed>  $data  Pre-validated resubmit payload
     */
    public static function resubmitApplication(User $user, array $data): User
    {
        if ($user->status !== UserStatus::ReturnedForCorrection) {
            throw ValidationException::withMessages([
                'user' => 'Only applications returned for correction can be resubmitted.',
            ]);
        }

        $updates = [
            'name' => $data['name'],
            'name_urdu' => $data['name_urdu'],
            'email' => $data['email'],
            'status' => UserStatus::Pending,
            'phones' => self::normalizePhones($data['phones'] ?? []),
            'bank_accounts' => self::normalizeBankAccounts($data['bank_accounts'] ?? []),
            'returned_at' => null,
            'returned_by' => null,
            'correction_remarks' => null,
            'rejected_at' => null,
            'rejected_by' => null,
            'rejection_reason' => null,
        ];

        if (filled($data['password'] ?? null)) {
            $updates['password'] = Hash::make($data['password']);
        }

        $user->update($updates);

        StaffFileStorage::storeForUser($user, [
            'profile_photo' => $data['profile_photo'] ?? null,
            'cnic_front' => $data['cnic_front'] ?? null,
            'cnic_back' => $data['cnic_back'] ?? null,
        ]);

        PermissionGate::flush($user);

        return $user->refresh();
    }

    public static function approve(User $user, Role $role, User $approver): User
    {
        if ($user->status !== UserStatus::Pending) {
            throw ValidationException::withMessages([
                'user' => 'Only pending users can be approved.',
            ]);
        }

        if ($role->isSuperAdmin() && ! $approver->isSuperAdmin()) {
            throw ValidationException::withMessages([
                'role_id' => 'Only a Super Admin can assign the Super Admin role.',
            ]);
        }

        if ($user->isSuperAdmin() && ! $approver->canManageSuperAdminAccounts()) {
            throw ValidationException::withMessages([
                'user' => 'Only a Super Admin can approve or manage Super Admin accounts.',
            ]);
        }

        if (! $role->is_active) {
            throw ValidationException::withMessages([
                'role_id' => 'The selected role is inactive.',
            ]);
        }

        $user->update([
            'role_id' => $role->id,
            'status' => UserStatus::Active,
            'approved_at' => now(),
            'approved_by' => $approver->id,
            'rejected_at' => null,
            'rejected_by' => null,
            'rejection_reason' => null,
            'returned_at' => null,
            'returned_by' => null,
            'correction_remarks' => null,
            'staff_no' => $user->staff_no ?? User::generateStaffNumber(),
        ]);

        PermissionGate::flush($user);

        return $user->refresh();
    }

    public static function returnForCorrection(User $user, User $approver, string $remarks): User
    {
        if ($user->status !== UserStatus::Pending) {
            throw ValidationException::withMessages([
                'user' => 'Only pending applications can be returned for correction.',
            ]);
        }

        $user->update([
            'status' => UserStatus::ReturnedForCorrection,
            'returned_at' => now(),
            'returned_by' => $approver->id,
            'correction_remarks' => $remarks,
            'rejected_at' => null,
            'rejected_by' => null,
            'rejection_reason' => null,
            'role_id' => null,
        ]);

        PermissionGate::flush($user);

        return $user->refresh();
    }

    public static function reject(User $user, User $approver, string $reason): User
    {
        if ($user->status !== UserStatus::Pending) {
            throw ValidationException::withMessages([
                'user' => 'Only pending users can be rejected.',
            ]);
        }

        if ($user->isSuperAdmin() && ! $approver->canManageSuperAdminAccounts()) {
            throw ValidationException::withMessages([
                'user' => 'Only a Super Admin can reject Super Admin accounts.',
            ]);
        }

        $user->update([
            'status' => UserStatus::Rejected,
            'rejected_at' => now(),
            'rejected_by' => $approver->id,
            'rejection_reason' => $reason,
            'returned_at' => null,
            'returned_by' => null,
            'correction_remarks' => null,
            'role_id' => null,
        ]);

        PermissionGate::flush($user);

        return $user->refresh();
    }

    /**
     * @return array<string, mixed>
     */
    public static function staffFormFromUser(User $user): array
    {
        $phones = $user->phones ?? [];
        $bankAccounts = $user->bank_accounts ?? [];

        $emergencyIndex = 0;
        $defaultIndex = 0;

        foreach ($phones as $index => $phone) {
            if ($phone['is_emergency'] ?? false) {
                $emergencyIndex = $index;
            }
            if ($phone['is_default'] ?? false) {
                $defaultIndex = $index;
            }
        }

        return [
            'name' => $user->name,
            'name_urdu' => $user->name_urdu,
            'email' => $user->email,
            'password' => '',
            'password_confirmation' => '',
            'phones' => $phones !== [] ? $phones : self::emptyStaffForm()['phones'],
            'bank_accounts' => $bankAccounts !== [] ? $bankAccounts : self::emptyStaffForm()['bank_accounts'],
            'emergency_phone_index' => $emergencyIndex,
            'default_phone_index' => $defaultIndex,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $phones
     * @return list<array<string, mixed>>
     */
    protected static function normalizePhones(array $phones): array
    {
        return collect($phones)
            ->filter(fn (array $phone): bool => filled($phone['mobile'] ?? null))
            ->values()
            ->all();
    }

    /**
     * @param  list<array<string, mixed>>  $bankAccounts
     * @return list<array<string, mixed>>
     */
    protected static function normalizeBankAccounts(array $bankAccounts): array
    {
        return collect($bankAccounts)
            ->filter(fn (array $account): bool => filled($account['bank_name'] ?? null)
                || filled($account['account_number'] ?? null)
                || filled($account['iban'] ?? null))
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public static function emptyStaffForm(?string $staffNo = null): array
    {
        return [
            'staff_no' => $staffNo ?? User::generateStaffNumber(),
            'join_date' => now()->format('d M Y'),
            'name' => '',
            'name_urdu' => '',
            'email' => '',
            'password' => '',
            'password_confirmation' => '',
            'phones' => [
                [
                    'mobile' => '',
                    'contact_person' => '',
                    'is_emergency' => true,
                    'is_default' => true,
                ],
            ],
            'bank_accounts' => [
                [
                    'bank_name' => '',
                    'account_title' => '',
                    'account_number' => '',
                    'iban' => '',
                ],
            ],
        ];
    }
}
