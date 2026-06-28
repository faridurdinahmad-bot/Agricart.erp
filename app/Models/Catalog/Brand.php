<?php

namespace App\Models\Catalog;

use App\Core\Ai\Concerns\HasAiContentStatus;
use App\Core\Ai\Enums\AiContentStatus;
use App\Core\Deletion\Enums\EntityDeletionRequestStatus;
use App\Models\User;
use App\Modules\Catalog\Enums\BrandLifecycleStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model
{
    use HasAiContentStatus;
    use SoftDeletes;

    protected $table = 'catalog_brands';

    protected $fillable = [
        'code',
        'name_en',
        'name_ur',
        'logo_path',
        'short_note',
        'short_description_en',
        'short_description_ur',
        'long_description_en',
        'long_description_ur',
        'brand_overview_en',
        'brand_overview_ur',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'country',
        'website',
        'is_active',
        'lifecycle_status',
        'ai_content_status',
        'content_reviewed_at',
        'content_reviewed_by',
        'last_ai_generated_at',
        'last_ai_model',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'lifecycle_status' => BrandLifecycleStatus::class,
            'last_ai_generated_at' => 'datetime',
            'content_reviewed_at' => 'datetime',
        ];
    }

    public function isPendingDeletion(): bool
    {
        return $this->lifecycle_status === BrandLifecycleStatus::PendingDeletion;
    }

    public function awaitingContentReview(): bool
    {
        return $this->ai_content_status === AiContentStatus::NeedsReview;
    }

    public function contentReviewedLabel(): string
    {
        if (! $this->content_reviewed_at) {
            return 'Not reviewed yet';
        }

        $reviewer = $this->contentReviewer?->name;
        $timestamp = $this->content_reviewed_at->format('d M Y, H:i');

        return $reviewer ? "{$timestamp} · {$reviewer}" : $timestamp;
    }

    public function lastAiGeneratedLabel(): string
    {
        if (! $this->last_ai_generated_at) {
            return 'Not generated yet';
        }

        $timestamp = $this->last_ai_generated_at->format('d M Y, H:i');
        $model = filled($this->last_ai_model) ? ' · '.$this->last_ai_model : '';

        return $timestamp.$model;
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function contentReviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'content_reviewed_by');
    }

    /**
     * @return BelongsToMany<Category, $this>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'catalog_brand_category', 'brand_id', 'category_id')
            ->withTimestamps()
            ->orderBy('catalog_categories.name_en');
    }

    /**
     * @return HasMany<BrandDeletionRequest, $this>
     */
    public function deletionRequests(): HasMany
    {
        return $this->hasMany(BrandDeletionRequest::class, 'brand_id')->orderByDesc('requested_at');
    }

    /**
     * @return HasOne<BrandDeletionRequest, $this>
     */
    public function pendingDeletionRequest(): HasOne
    {
        return $this->hasOne(BrandDeletionRequest::class, 'brand_id')
            ->where('status', EntityDeletionRequestStatus::Pending)
            ->latestOfMany('requested_at');
    }
}
