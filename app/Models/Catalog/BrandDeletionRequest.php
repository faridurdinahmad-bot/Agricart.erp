<?php

namespace App\Models\Catalog;

use App\Core\Deletion\Enums\EntityDeletionRequestStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BrandDeletionRequest extends Model
{
    protected $table = 'catalog_brand_deletion_requests';

    protected $fillable = [
        'brand_id',
        'status',
        'reason',
        'was_active_before_request',
        'requested_by',
        'requested_at',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => EntityDeletionRequestStatus::class,
            'was_active_before_request' => 'boolean',
            'requested_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function isPending(): bool
    {
        return $this->status === EntityDeletionRequestStatus::Pending;
    }

    /**
     * @return BelongsTo<Brand, $this>
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function requestedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function reviewedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
