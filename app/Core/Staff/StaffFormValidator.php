<?php

namespace App\Core\Staff;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

final class StaffFormValidator
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return self::baseRules(requirePassword: true, requireFiles: true);
    }

    /**
     * @return array<string, mixed>
     */
    public static function rulesForUpdate(?int $userId = null, bool $requireFiles = false): array
    {
        return self::baseRules(
            requirePassword: false,
            requireFiles: $requireFiles,
            userId: $userId,
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected static function baseRules(bool $requirePassword, bool $requireFiles, ?int $userId = null): array
    {
        $emailRule = ['required', 'email', 'max:255'];

        if ($userId) {
            $emailRule[] = Rule::unique('users', 'email')->ignore($userId);
        } else {
            $emailRule[] = 'unique:users,email';
        }

        $passwordRules = $requirePassword
            ? ['required', 'confirmed', Password::defaults()]
            : ['nullable', 'confirmed', Password::defaults()];

        $passwordConfirmationRules = $requirePassword
            ? ['required', 'string']
            : ['nullable', 'string'];

        $fileRules = fn (string $field): array => $requireFiles
            ? ['required', 'file', 'mimes:webp', 'max:'.($field === 'profilePhoto' ? '5120' : '10240')]
            : ['nullable', 'file', 'mimes:webp', 'max:'.($field === 'profilePhoto' ? '5120' : '10240')];

        return [
            'staffForm.name' => ['required', 'string', 'max:255'],
            'staffForm.name_urdu' => ['required', 'string', 'max:255'],
            'staffForm.email' => $emailRule,
            'staffForm.password' => $passwordRules,
            'staffForm.password_confirmation' => $passwordConfirmationRules,
            'staffForm.phones' => ['required', 'array', 'min:1'],
            'staffForm.phones.*.mobile' => ['required', 'string', 'max:50'],
            'staffForm.phones.*.contact_person' => ['required', 'string', 'max:255'],
            'staffForm.bank_accounts' => ['required', 'array', 'min:1'],
            'staffForm.bank_accounts.*.bank_name' => ['required', 'string', 'max:255'],
            'staffForm.bank_accounts.*.account_title' => ['required', 'string', 'max:255'],
            'staffForm.bank_accounts.*.account_number' => ['required', 'string', 'max:255'],
            'staffForm.bank_accounts.*.iban' => ['required', 'string', 'max:255'],
            'profilePhoto' => $fileRules('profilePhoto'),
            'cnicFront' => $fileRules('cnicFront'),
            'cnicBack' => $fileRules('cnicBack'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function messages(): array
    {
        return [
            'staffForm.name.required' => 'Full Name (English) is required.',
            'staffForm.name_urdu.required' => 'Full Name (Urdu) is required.',
            'staffForm.email.required' => 'Email is required.',
            'staffForm.email.email' => 'Enter a valid email address.',
            'staffForm.email.unique' => 'This email is already registered.',
            'staffForm.password.required' => 'Password is required.',
            'staffForm.password.confirmed' => 'Password confirmation does not match.',
            'staffForm.password_confirmation.required' => 'Confirm password is required.',
            'staffForm.phones.required' => 'At least one phone number is required.',
            'staffForm.phones.min' => 'At least one phone number is required.',
            'staffForm.phones.*.mobile.required' => 'Mobile number is required.',
            'staffForm.phones.*.contact_person.required' => 'Contact person name is required.',
            'staffForm.bank_accounts.required' => 'At least one bank account is required.',
            'staffForm.bank_accounts.min' => 'At least one bank account is required.',
            'staffForm.bank_accounts.*.bank_name.required' => 'Bank name is required.',
            'staffForm.bank_accounts.*.account_title.required' => 'Account title is required.',
            'staffForm.bank_accounts.*.account_number.required' => 'Account number is required.',
            'staffForm.bank_accounts.*.iban.required' => 'IBAN is required.',
            'profilePhoto.required' => 'Profile photo is required.',
            'profilePhoto.mimes' => 'Profile photo must be a WebP image. JPG, PNG, GIF, BMP and other formats are not allowed.',
            'cnicFront.required' => 'CNIC front image is required.',
            'cnicFront.mimes' => 'CNIC front must be a WebP image. JPG, PNG, GIF, BMP and other formats are not allowed.',
            'cnicBack.required' => 'CNIC back image is required.',
            'cnicBack.mimes' => 'CNIC back must be a WebP image. JPG, PNG, GIF, BMP and other formats are not allowed.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function attributes(): array
    {
        return [
            'staffForm.name' => 'Full Name (English)',
            'staffForm.name_urdu' => 'Full Name (Urdu)',
            'staffForm.email' => 'Email',
            'staffForm.password' => 'Password',
            'profilePhoto' => 'Profile Photo',
            'cnicFront' => 'CNIC Front',
            'cnicBack' => 'CNIC Back',
        ];
    }
}
