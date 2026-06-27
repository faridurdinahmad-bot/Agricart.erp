<?php

namespace App\Models;

use App\Core\Authorization\Concerns\HasAgricartPermissions;
use App\Core\Authorization\Enums\UserRegistrationSource;
use App\Core\Authorization\Enums\UserStatus;
use App\Core\Staff\StaffFileStorage;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'staff_no',
    'name',
    'name_urdu',
    'email',
    'password',
    'status',
    'role_id',
    'join_date',
    'approved_at',
    'approved_by',
    'rejected_at',
    'rejected_by',
    'rejection_reason',
    'returned_at',
    'returned_by',
    'correction_remarks',
    'registration_source',
    'phones',
    'bank_accounts',
    'profile_photo_path',
    'cnic_front_path',
    'cnic_back_path',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasAgricartPermissions, HasFactory, Notifiable;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => UserStatus::class,
            'registration_source' => UserRegistrationSource::class,
            'join_date' => 'date',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'returned_at' => 'datetime',
            'phones' => 'array',
            'bank_accounts' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Role, $this>
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(self::class, 'approved_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function rejector(): BelongsTo
    {
        return $this->belongsTo(self::class, 'rejected_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function returner(): BelongsTo
    {
        return $this->belongsTo(self::class, 'returned_by');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->status === UserStatus::Active
            && $this->role_id !== null
            && $this->role?->is_active;
    }

    public function isPending(): bool
    {
        return $this->status === UserStatus::Pending;
    }

    public function isActive(): bool
    {
        return $this->status === UserStatus::Active;
    }

    public function isRejected(): bool
    {
        return $this->status === UserStatus::Rejected;
    }

    public function isReturnedForCorrection(): bool
    {
        return $this->status === UserStatus::ReturnedForCorrection;
    }

    public function requiresRegistrationStatusPage(): bool
    {
        return in_array($this->status, [
            UserStatus::Pending,
            UserStatus::ReturnedForCorrection,
            UserStatus::Rejected,
        ], true);
    }

    public function cnicFrontUrl(): ?string
    {
        return StaffFileStorage::url($this->cnic_front_path);
    }

    public function cnicBackUrl(): ?string
    {
        return StaffFileStorage::url($this->cnic_back_path);
    }

    public function profilePhotoUrl(): ?string
    {
        return StaffFileStorage::url($this->profile_photo_path);
    }

    public static function generateStaffNumber(): string
    {
        $latest = self::query()
            ->whereNotNull('staff_no')
            ->orderByDesc('id')
            ->value('staff_no');

        if ($latest && preg_match('/STF-(\d+)/', $latest, $matches)) {
            return 'STF-'.(((int) $matches[1]) + 1);
        }

        return 'STF-'.(self::query()->count() + 1);
    }
}
