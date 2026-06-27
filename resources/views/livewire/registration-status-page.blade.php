<div class="agricart-register">
    <header class="agricart-register__header">
        <div class="agricart-register__brand">Agricart</div>
        <p class="agricart-register__subtitle">Registration Status</p>
    </header>

    <div class="agricart-register__status">
        @if ($status === \App\Core\Authorization\Enums\UserStatus::Pending)
            <h2>Your registration is under review.</h2>
            <p>Thank you for registering. Our team is reviewing your application. You will be notified once a decision has been made.</p>
        @elseif ($status === \App\Core\Authorization\Enums\UserStatus::ReturnedForCorrection)
            <h2>Application returned for correction</h2>
            <p>Please review the admin remarks below, update your application, and submit again.</p>

            @if (filled($user->correction_remarks))
                <div class="agricart-register__remarks">
                    <strong>Admin remarks</strong>
                    <p>{{ $user->correction_remarks }}</p>
                </div>
            @endif

            <div class="agricart-register__actions agricart-register__actions--start">
                <a href="{{ route('register', ['edit' => 1]) }}" class="agricart-register__btn">Edit Application</a>
            </div>
        @elseif ($status === \App\Core\Authorization\Enums\UserStatus::Rejected)
            <h2>Registration rejected</h2>
            <p>Unfortunately, your registration has been rejected and you cannot access the Admin Panel.</p>

            @if (filled($user->rejection_reason))
                <div class="agricart-register__remarks agricart-register__remarks--danger">
                    <strong>Reason</strong>
                    <p>{{ $user->rejection_reason }}</p>
                </div>
            @endif
        @endif

        <div class="agricart-register__status-footer">
            <button type="button" class="agricart-register__link-btn" wire:click="logout">Sign out</button>
        </div>
    </div>
</div>
