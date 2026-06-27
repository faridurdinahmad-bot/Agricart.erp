@php
    /** @var \App\Models\User $profileUser */
    $profileUser = $profileUser ?? null;
@endphp

@if ($profileUser)
    <div class="agricart-staff-profile">
        <section class="agricart-staff-profile__section">
            <h3 class="agricart-staff-profile__title">Staff Information</h3>
            <dl class="agricart-staff-profile__grid">
                <div><dt>Staff No.</dt><dd>{{ $profileUser->staff_no ?? '—' }}</dd></div>
                <div><dt>Join Date</dt><dd>{{ $profileUser->join_date?->format('Y-m-d') ?? '—' }}</dd></div>
                <div><dt>Full Name (English)</dt><dd>{{ $profileUser->name }}</dd></div>
                <div><dt>Full Name (Urdu)</dt><dd dir="rtl">{{ $profileUser->name_urdu }}</dd></div>
                <div><dt>Email</dt><dd>{{ $profileUser->email }}</dd></div>
                <div><dt>Source</dt><dd>{{ str($profileUser->registration_source->value)->headline() }}</dd></div>
                <div><dt>Status</dt><dd>{{ $profileUser->status->label() }}</dd></div>
            </dl>
        </section>

        <section class="agricart-staff-profile__section">
            <h3 class="agricart-staff-profile__title">Contact</h3>
            @forelse ($profileUser->phones ?? [] as $phone)
                <dl class="agricart-staff-profile__grid agricart-staff-profile__grid--2">
                    <div><dt>Mobile</dt><dd>{{ $phone['mobile'] ?? '—' }}</dd></div>
                    <div><dt>Contact Person</dt><dd>{{ $phone['contact_person'] ?? '—' }}</dd></div>
                    <div><dt>Emergency</dt><dd>{{ ($phone['is_emergency'] ?? false) ? 'Yes' : 'No' }}</dd></div>
                    <div><dt>Default</dt><dd>{{ ($phone['is_default'] ?? false) ? 'Yes' : 'No' }}</dd></div>
                </dl>
            @empty
                <p class="agricart-staff-profile__empty">No phone numbers provided.</p>
            @endforelse
        </section>

        <section class="agricart-staff-profile__section">
            <h3 class="agricart-staff-profile__title">Identity Documents</h3>
            <div class="agricart-staff-profile__images">
                <figure>
                    <figcaption>Profile Photo</figcaption>
                    @if ($profileUser->profilePhotoUrl())
                        <img src="{{ $profileUser->profilePhotoUrl() }}" alt="Profile photo">
                    @else
                        <p class="agricart-staff-profile__empty">Not uploaded</p>
                    @endif
                </figure>
                <figure>
                    <figcaption>CNIC Front</figcaption>
                    @if ($profileUser->cnicFrontUrl())
                        <img src="{{ $profileUser->cnicFrontUrl() }}" alt="CNIC front">
                    @else
                        <p class="agricart-staff-profile__empty">Not uploaded</p>
                    @endif
                </figure>
                <figure>
                    <figcaption>CNIC Back</figcaption>
                    @if ($profileUser->cnicBackUrl())
                        <img src="{{ $profileUser->cnicBackUrl() }}" alt="CNIC back">
                    @else
                        <p class="agricart-staff-profile__empty">Not uploaded</p>
                    @endif
                </figure>
            </div>
        </section>

        <section class="agricart-staff-profile__section">
            <h3 class="agricart-staff-profile__title">Bank Accounts</h3>
            @forelse ($profileUser->bank_accounts ?? [] as $account)
                <dl class="agricart-staff-profile__grid agricart-staff-profile__grid--2">
                    <div><dt>Bank Name</dt><dd>{{ $account['bank_name'] ?? '—' }}</dd></div>
                    <div><dt>Account Title</dt><dd>{{ $account['account_title'] ?? '—' }}</dd></div>
                    <div><dt>Account Number</dt><dd>{{ $account['account_number'] ?? '—' }}</dd></div>
                    <div><dt>IBAN</dt><dd>{{ $account['iban'] ?? '—' }}</dd></div>
                </dl>
            @empty
                <p class="agricart-staff-profile__empty">No bank accounts provided.</p>
            @endforelse
        </section>
    </div>
@endif
