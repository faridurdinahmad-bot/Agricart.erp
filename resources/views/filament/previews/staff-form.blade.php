@php
    $live = $live ?? false;
    $showStaffNumber = $showStaffNumber ?? true;
    $showJoinDate = $showJoinDate ?? true;
    $staffForm = $staffForm ?? null;
    $errors ??= new \Illuminate\Support\ViewErrorBag;
    $profilePhoto = $profilePhoto ?? null;
    $cnicFront = $cnicFront ?? null;
    $cnicBack = $cnicBack ?? null;
    $existingProfilePhotoUrl = $existingProfilePhotoUrl ?? null;
    $existingCnicFrontUrl = $existingCnicFrontUrl ?? null;
    $existingCnicBackUrl = $existingCnicBackUrl ?? null;

    $invalidClass = static function (string $key) use ($live, $errors): string {
        if (! $live) {
            return '';
        }

        return $errors->has($key) ? 'agricart-staff-form-preview__control--invalid' : '';
    };
@endphp

<div
    class="agricart-staff-form-preview"
    @unless($live)
        x-data="{
            phoneRows: [0],
            bankRows: [0],
            showPassword: false,
            showConfirmPassword: false,
            addPhone() { this.phoneRows.push(this.phoneRows.length); },
            removePhone(index) { if (this.phoneRows.length > 1) this.phoneRows.splice(index, 1); },
            addBank() { this.bankRows.push(this.bankRows.length); },
            removeBank(index) { if (this.bankRows.length > 1) this.bankRows.splice(index, 1); },
        }"
    @else
        x-data="{
            showPassword: false,
            showConfirmPassword: false,
        }"
    @endunless
>
    {{-- Staff + Login (compact) --}}
    <section class="agricart-staff-form-preview__section">
        <header class="agricart-staff-form-preview__section-header">
            <h3 class="agricart-staff-form-preview__section-title">Staff Information</h3>
        </header>
        <div class="agricart-staff-form-preview__section-body">
            <div @class([
                'agricart-staff-form-preview__grid',
                'agricart-staff-form-preview__grid--4' => $showStaffNumber || $showJoinDate,
                'agricart-staff-form-preview__grid--2' => ! $showStaffNumber && ! $showJoinDate,
            ])>
                @if ($showStaffNumber)
                    <div class="agricart-staff-form-preview__field">
                        <label class="agricart-staff-form-preview__label" for="staff_no_preview">Staff No.</label>
                        <input
                            class="agricart-staff-form-preview__control"
                            id="staff_no_preview"
                            type="text"
                            value="{{ $live ? ($staffForm['staff_no'] ?? '') : 'STF-4' }}"
                            readonly
                            disabled
                        >
                    </div>
                @endif
                @if ($showJoinDate)
                    <div class="agricart-staff-form-preview__field">
                        <label class="agricart-staff-form-preview__label" for="join_date_preview">Join Date</label>
                        <input
                            class="agricart-staff-form-preview__control"
                            id="join_date_preview"
                            type="text"
                            value="{{ $live ? ($staffForm['join_date'] ?? now()->format('d M Y')) : now()->format('d M Y') }}"
                            readonly
                            disabled
                        >
                    </div>
                @endif
                <div class="agricart-staff-form-preview__field">
                    <label class="agricart-staff-form-preview__label agricart-staff-form-preview__label--required" for="full_name_preview">Full Name (English)</label>
                    <input
                        @class(['agricart-staff-form-preview__control', $invalidClass('staffForm.name')])
                        id="full_name_preview"
                        type="text"
                        placeholder="Full name"
                        @if($live) wire:model.blur="staffForm.name" @endif
                    >
                    @if($live) @error('staffForm.name') <span class="agricart-staff-form-preview__error">{{ $message }}</span> @enderror @endif
                </div>
                <div class="agricart-staff-form-preview__field">
                    <label class="agricart-staff-form-preview__label agricart-staff-form-preview__label--required" for="full_name_urdu_preview">Full Name (Urdu)</label>
                    <input
                        @class(['agricart-staff-form-preview__control', $invalidClass('staffForm.name_urdu')])
                        id="full_name_urdu_preview"
                        type="text"
                        placeholder="مکمل نام"
                        dir="rtl"
                        lang="ur"
                        @if($live) wire:model.blur="staffForm.name_urdu" @endif
                    >
                    @if($live) @error('staffForm.name_urdu') <span class="agricart-staff-form-preview__error">{{ $message }}</span> @enderror @endif
                </div>
            </div>
            <div class="agricart-staff-form-preview__grid agricart-staff-form-preview__grid--3 agricart-staff-form-preview__grid--tight-top">
                <div class="agricart-staff-form-preview__field">
                    <label class="agricart-staff-form-preview__label agricart-staff-form-preview__label--required" for="email_preview">Email (Username)</label>
                    <input
                        @class(['agricart-staff-form-preview__control', $invalidClass('staffForm.email')])
                        id="email_preview"
                        type="email"
                        placeholder="name@agricart.test"
                        autocomplete="username"
                        @if($live) wire:model.blur="staffForm.email" @endif
                    >
                    @if($live) @error('staffForm.email') <span class="agricart-staff-form-preview__error">{{ $message }}</span> @enderror @endif
                </div>
                <div class="agricart-staff-form-preview__field">
                    <label class="agricart-staff-form-preview__label agricart-staff-form-preview__label--required" for="password_preview">Password</label>
                    <div class="agricart-staff-form-preview__password-wrap">
                        <input
                            @class(['agricart-staff-form-preview__control', 'agricart-staff-form-preview__control--password', $invalidClass('staffForm.password')])
                            id="password_preview"
                            :type="showPassword ? 'text' : 'password'"
                            placeholder="Password"
                            autocomplete="new-password"
                            @if($live) wire:model.blur="staffForm.password" @endif
                        >
                        <button type="button" class="agricart-staff-form-preview__password-toggle" x-on:click="showPassword = !showPassword" :aria-label="showPassword ? 'Hide password' : 'Show password'">
                            <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            <svg x-show="showPassword" x-cloak xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12c1.292 2.456 4.646 5.004 10.066 5.004 1.042 0 2.039-.137 2.981-.385M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639-.417 1.236-1.17 2.348-2.156 3.221M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>
                    @if($live) @error('staffForm.password') <span class="agricart-staff-form-preview__error">{{ $message }}</span> @enderror @endif
                </div>
                <div class="agricart-staff-form-preview__field">
                    <label class="agricart-staff-form-preview__label agricart-staff-form-preview__label--required" for="password_confirm_preview">Confirm Password</label>
                    <div class="agricart-staff-form-preview__password-wrap">
                        <input
                            @class(['agricart-staff-form-preview__control', 'agricart-staff-form-preview__control--password', $invalidClass('staffForm.password_confirmation')])
                            id="password_confirm_preview"
                            :type="showConfirmPassword ? 'text' : 'password'"
                            placeholder="Confirm password"
                            autocomplete="new-password"
                            @if($live) wire:model.blur="staffForm.password_confirmation" @endif
                        >
                        <button type="button" class="agricart-staff-form-preview__password-toggle" x-on:click="showConfirmPassword = !showConfirmPassword" :aria-label="showConfirmPassword ? 'Hide password' : 'Show password'">
                            <svg x-show="!showConfirmPassword" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            <svg x-show="showConfirmPassword" x-cloak xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12c1.292 2.456 4.646 5.004 10.066 5.004 1.042 0 2.039-.137 2.981-.385M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639-.417 1.236-1.17 2.348-2.156 3.221M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>
                    @if($live) @error('staffForm.password_confirmation') <span class="agricart-staff-form-preview__error">{{ $message }}</span> @enderror @endif
                </div>
            </div>
        </div>
    </section>

    {{-- Contact --}}
    <section class="agricart-staff-form-preview__section">
        <header class="agricart-staff-form-preview__section-header">
            <h3 class="agricart-staff-form-preview__section-title">Contact</h3>
        </header>
        <div class="agricart-staff-form-preview__section-body">
            <div class="agricart-staff-form-preview__repeater">
                @if ($live)
                    @foreach ($staffForm['phones'] ?? [] as $index => $phone)
                        <div class="agricart-staff-form-preview__repeater-row agricart-staff-form-preview__repeater-row--phone" wire:key="phone-{{ $index }}">
                            <div class="agricart-staff-form-preview__field">
                                <label class="agricart-staff-form-preview__label agricart-staff-form-preview__label--required">Mobile Number</label>
                                <input @class(['agricart-staff-form-preview__control', $invalidClass('staffForm.phones.'.$index.'.mobile')]) type="tel" placeholder="+92 300 0000000" wire:model.blur="staffForm.phones.{{ $index }}.mobile">
                                @error('staffForm.phones.'.$index.'.mobile') <span class="agricart-staff-form-preview__error">{{ $message }}</span> @enderror
                            </div>
                            <div class="agricart-staff-form-preview__field">
                                <label class="agricart-staff-form-preview__label agricart-staff-form-preview__label--required">Contact Person Name</label>
                                <input @class(['agricart-staff-form-preview__control', $invalidClass('staffForm.phones.'.$index.'.contact_person')]) type="text" placeholder="e.g. Mother, Brother" wire:model.blur="staffForm.phones.{{ $index }}.contact_person">
                                @error('staffForm.phones.'.$index.'.contact_person') <span class="agricart-staff-form-preview__error">{{ $message }}</span> @enderror
                            </div>
                            <label class="agricart-staff-form-preview__default">
                                <input type="radio" name="emergency_phone_live" value="{{ $index }}" wire:model="staffForm.emergency_phone_index">
                                Emergency
                            </label>
                            <label class="agricart-staff-form-preview__default">
                                <input type="radio" name="default_phone_live" value="{{ $index }}" wire:model="staffForm.default_phone_index">
                                Default
                            </label>
                            <button type="button" class="agricart-staff-form-preview__remove" aria-label="Remove phone" @if(count($staffForm['phones'] ?? []) <= 1) disabled @endif wire:click="removePhoneRow({{ $index }})">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    @endforeach
                @else
                    <template x-for="(row, index) in phoneRows" :key="'phone-' + index">
                        <div class="agricart-staff-form-preview__repeater-row agricart-staff-form-preview__repeater-row--phone">
                            <div class="agricart-staff-form-preview__field">
                                <label class="agricart-staff-form-preview__label agricart-staff-form-preview__label--required">Mobile Number</label>
                                <input class="agricart-staff-form-preview__control" type="tel" placeholder="+92 300 0000000">
                            </div>
                            <div class="agricart-staff-form-preview__field">
                                <label class="agricart-staff-form-preview__label agricart-staff-form-preview__label--required">Contact Person Name</label>
                                <input class="agricart-staff-form-preview__control" type="text" placeholder="e.g. Mother, Brother">
                            </div>
                            <label class="agricart-staff-form-preview__default">
                                <input type="radio" name="emergency_phone_preview" :value="index" :checked="index === 0">
                                Emergency
                            </label>
                            <label class="agricart-staff-form-preview__default">
                                <input type="radio" name="default_phone_preview" :value="index" :checked="index === 0">
                                Default
                            </label>
                            <button type="button" class="agricart-staff-form-preview__remove" aria-label="Remove phone" x-show="phoneRows.length > 1" x-on:click="removePhone(index)">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </template>
                @endif
            </div>
            @if($live) @error('staffForm.phones') <span class="agricart-staff-form-preview__error">{{ $message }}</span> @enderror @endif
            <button type="button" class="agricart-staff-form-preview__add-link" @if($live) wire:click="addPhoneRow" @else x-on:click="addPhone()" @endif>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Add Phone
            </button>
        </div>
    </section>

    {{-- Identity --}}
    <section class="agricart-staff-form-preview__section">
        <header class="agricart-staff-form-preview__section-header">
            <h3 class="agricart-staff-form-preview__section-title">Identity</h3>
        </header>
        <div class="agricart-staff-form-preview__section-body">
            <div class="agricart-staff-form-preview__grid agricart-staff-form-preview__grid--3">
                <div class="agricart-staff-form-preview__field">
                    <span class="agricart-staff-form-preview__label agricart-staff-form-preview__label--required">Profile Photo (WebP only)</span>
                    @include('filament.previews.partials.staff-form-upload', [
                        'live' => $live,
                        'wireModel' => 'profilePhoto',
                        'errorKey' => 'profilePhoto',
                        'alt' => 'Profile preview',
                        'file' => $profilePhoto,
                        'existingUrl' => $existingProfilePhotoUrl ?? null,
                        'errors' => $errors,
                    ])
                </div>
                <div class="agricart-staff-form-preview__field">
                    <span class="agricart-staff-form-preview__label agricart-staff-form-preview__label--required">CNIC Front (WebP only)</span>
                    @include('filament.previews.partials.staff-form-upload', [
                        'live' => $live,
                        'wireModel' => 'cnicFront',
                        'errorKey' => 'cnicFront',
                        'alt' => 'CNIC front preview',
                        'file' => $cnicFront,
                        'existingUrl' => $existingCnicFrontUrl ?? null,
                        'errors' => $errors,
                    ])
                </div>
                <div class="agricart-staff-form-preview__field">
                    <span class="agricart-staff-form-preview__label agricart-staff-form-preview__label--required">CNIC Back (WebP only)</span>
                    @include('filament.previews.partials.staff-form-upload', [
                        'live' => $live,
                        'wireModel' => 'cnicBack',
                        'errorKey' => 'cnicBack',
                        'alt' => 'CNIC back preview',
                        'file' => $cnicBack,
                        'existingUrl' => $existingCnicBackUrl ?? null,
                        'errors' => $errors,
                    ])
                </div>
            </div>
        </div>
    </section>

    {{-- Bank Accounts --}}
    <section class="agricart-staff-form-preview__section">
        <header class="agricart-staff-form-preview__section-header">
            <h3 class="agricart-staff-form-preview__section-title">Bank Accounts</h3>
        </header>
        <div class="agricart-staff-form-preview__section-body">
            <div class="agricart-staff-form-preview__repeater">
                @if ($live)
                    @foreach ($staffForm['bank_accounts'] ?? [] as $index => $account)
                        <div class="agricart-staff-form-preview__repeater-row agricart-staff-form-preview__repeater-row--bank" wire:key="bank-{{ $index }}">
                            <div class="agricart-staff-form-preview__field">
                                <label class="agricart-staff-form-preview__label agricart-staff-form-preview__label--required">Bank Name</label>
                                <input @class(['agricart-staff-form-preview__control', $invalidClass('staffForm.bank_accounts.'.$index.'.bank_name')]) type="text" placeholder="Bank" wire:model.blur="staffForm.bank_accounts.{{ $index }}.bank_name">
                                @error('staffForm.bank_accounts.'.$index.'.bank_name') <span class="agricart-staff-form-preview__error">{{ $message }}</span> @enderror
                            </div>
                            <div class="agricart-staff-form-preview__field">
                                <label class="agricart-staff-form-preview__label agricart-staff-form-preview__label--required">Account Title</label>
                                <input @class(['agricart-staff-form-preview__control', $invalidClass('staffForm.bank_accounts.'.$index.'.account_title')]) type="text" placeholder="Title" wire:model.blur="staffForm.bank_accounts.{{ $index }}.account_title">
                                @error('staffForm.bank_accounts.'.$index.'.account_title') <span class="agricart-staff-form-preview__error">{{ $message }}</span> @enderror
                            </div>
                            <div class="agricart-staff-form-preview__field">
                                <label class="agricart-staff-form-preview__label agricart-staff-form-preview__label--required">Account Number</label>
                                <input @class(['agricart-staff-form-preview__control', $invalidClass('staffForm.bank_accounts.'.$index.'.account_number')]) type="text" placeholder="Number" wire:model.blur="staffForm.bank_accounts.{{ $index }}.account_number">
                                @error('staffForm.bank_accounts.'.$index.'.account_number') <span class="agricart-staff-form-preview__error">{{ $message }}</span> @enderror
                            </div>
                            <div class="agricart-staff-form-preview__field">
                                <label class="agricart-staff-form-preview__label agricart-staff-form-preview__label--required">IBAN</label>
                                <input @class(['agricart-staff-form-preview__control', $invalidClass('staffForm.bank_accounts.'.$index.'.iban')]) type="text" placeholder="IBAN" wire:model.blur="staffForm.bank_accounts.{{ $index }}.iban">
                                @error('staffForm.bank_accounts.'.$index.'.iban') <span class="agricart-staff-form-preview__error">{{ $message }}</span> @enderror
                            </div>
                            <button type="button" class="agricart-staff-form-preview__remove" aria-label="Remove bank account" @if(count($staffForm['bank_accounts'] ?? []) <= 1) disabled @endif wire:click="removeBankRow({{ $index }})">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    @endforeach
                @else
                    <template x-for="(row, index) in bankRows" :key="'bank-' + index">
                        <div class="agricart-staff-form-preview__repeater-row agricart-staff-form-preview__repeater-row--bank">
                            <div class="agricart-staff-form-preview__field">
                                <label class="agricart-staff-form-preview__label agricart-staff-form-preview__label--required">Bank Name</label>
                                <input class="agricart-staff-form-preview__control" type="text" placeholder="Bank">
                            </div>
                            <div class="agricart-staff-form-preview__field">
                                <label class="agricart-staff-form-preview__label agricart-staff-form-preview__label--required">Account Title</label>
                                <input class="agricart-staff-form-preview__control" type="text" placeholder="Title">
                            </div>
                            <div class="agricart-staff-form-preview__field">
                                <label class="agricart-staff-form-preview__label agricart-staff-form-preview__label--required">Account Number</label>
                                <input class="agricart-staff-form-preview__control" type="text" placeholder="Number">
                            </div>
                            <div class="agricart-staff-form-preview__field">
                                <label class="agricart-staff-form-preview__label agricart-staff-form-preview__label--required">IBAN</label>
                                <input class="agricart-staff-form-preview__control" type="text" placeholder="IBAN">
                            </div>
                            <button type="button" class="agricart-staff-form-preview__remove" aria-label="Remove bank account" x-show="bankRows.length > 1" x-on:click="removeBank(index)">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </template>
                @endif
            </div>
            @if($live) @error('staffForm.bank_accounts') <span class="agricart-staff-form-preview__error">{{ $message }}</span> @enderror @endif
            <button type="button" class="agricart-staff-form-preview__add-link" @if($live) wire:click="addBankRow" @else x-on:click="addBank()" @endif>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Add Bank Account
            </button>
        </div>
    </section>
</div>
