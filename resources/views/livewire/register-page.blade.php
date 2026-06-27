<div class="agricart-register">
    <header class="agricart-register__header">
        <div class="agricart-register__brand">Agricart</div>
        <p class="agricart-register__subtitle">
            @if ($isEditing)
                Edit Application
            @else
                Staff Registration
            @endif
        </p>
    </header>

    @if ($submitted)
        <div class="agricart-register__success">
            @if ($resubmitted)
                <h2>Application updated</h2>
                <p>Your corrected application has been resubmitted and is pending review again.</p>
                <a href="{{ route('registration.status') }}" class="agricart-register__btn">View Status</a>
            @else
                <h2>Registration submitted</h2>
                <p>Your account is pending approval. A Super Admin will assign your role and activate your account. You will be able to log in once approved.</p>
                <a href="{{ route('filament.admin.auth.login') }}" class="agricart-register__btn">Go to Login</a>
            @endif
        </div>
    @else
        <form wire:submit="register" class="agricart-register__form">
            @if ($isEditing)
                <div class="agricart-register__notice">
                    Update the fields noted in the admin remarks. Leave password blank to keep your current password. Upload new images only if you need to replace them.
                </div>
            @endif

            @include('filament.previews.staff-form', [
                'live' => true,
                'showStaffNumber' => false,
                'showJoinDate' => false,
                'staffForm' => $staffForm,
                'profilePhoto' => $profilePhoto,
                'cnicFront' => $cnicFront,
                'cnicBack' => $cnicBack,
                'existingProfilePhotoUrl' => $existingProfilePhotoUrl,
                'existingCnicFrontUrl' => $existingCnicFrontUrl,
                'existingCnicBackUrl' => $existingCnicBackUrl,
            ])

            <div class="agricart-register__actions">
                @if ($isEditing)
                    <a href="{{ route('registration.status') }}" class="agricart-register__btn agricart-register__btn--secondary">Cancel</a>
                @endif
                <button type="submit" class="agricart-register__btn">
                    {{ $isEditing ? 'Resubmit Application' : 'Submit Registration' }}
                </button>
            </div>
        </form>
    @endif
</div>
