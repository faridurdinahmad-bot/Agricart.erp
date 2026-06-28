<?php

namespace App\Models\Catalog;

use App\Core\Ai\Concerns\HasAiContentStatus;
use App\Core\Ai\Enums\AiContentStatus;
use App\Core\Deletion\Enums\EntityDeletionRequestStatus;
use App\Models\User;
use App\Modules\Catalog\Enums\CategoryLifecycleStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasAiContentStatus;
    use SoftDeletes;

    protected $table = 'catalog_categories';

    protected $fillable = [
        'parent_id',
        'code',
        'name_en',
        'name_ur',
        'image_path',
        'hs_code',
        'display_order',
        'is_active',
        'lifecycle_status',
        'ai_content_status',
        'content_reviewed_at',
        'content_reviewed_by',
        'short_description_en',
        'short_description_ur',
        'long_description_en',
        'long_description_ur',
        'usage_en',
        'usage_ur',
        'benefits_en',
        'benefits_ur',
        'warnings_en',
        'warnings_ur',
        'seo_title',
        'seo_focus_keyword_en',
        'seo_focus_keyword_ur',
        'meta_description',
        'meta_keywords',
        'url_slug',
        'canonical_url',
        'meta_robots',
        'og_title',
        'og_description',
        'synonyms_en',
        'synonyms_ur',
        'alternate_spellings',
        'search_aliases',
        'ai_prompt_override',
        'internal_tags',
        'google_category',
        'facebook_category',
        'last_ai_generated_at',
        'last_ai_model',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'display_order' => 'integer',
            'lifecycle_status' => CategoryLifecycleStatus::class,
            'last_ai_generated_at' => 'datetime',
            'content_reviewed_at' => 'datetime',
        ];
    }

    public function isPendingDeletion(): bool
    {
        return $this->lifecycle_status === CategoryLifecycleStatus::PendingDeletion;
    }

    public function lifecycleStatusLabel(): string
    {
        return $this->lifecycle_status?->label() ?? CategoryLifecycleStatus::Active->label();
    }

    public function lifecycleStatusBadgeClass(): string
    {
        return match ($this->lifecycle_status) {
            CategoryLifecycleStatus::PendingDeletion => 'agricart-users-list__badge--pending',
            CategoryLifecycleStatus::Deleted => 'agricart-users-list__badge--inactive',
            default => 'agricart-users-list__badge--active',
        };
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

    /**
     * @return BelongsTo<User, $this>
     */
    public function contentReviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'content_reviewed_by');
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany<Category, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('display_order')->orderBy('name_en');
    }

    /**
     * @return BelongsToMany<Brand, $this>
     */
    public function brands(): BelongsToMany
    {
        return $this->belongsToMany(Brand::class, 'catalog_brand_category', 'category_id', 'brand_id')
            ->withTimestamps()
            ->orderBy('catalog_brands.name_en');
    }

    /**
     * @return HasMany<CategoryUrlRedirect, $this>
     */
    public function urlRedirects(): HasMany
    {
        return $this->hasMany(CategoryUrlRedirect::class, 'category_id')->orderByDesc('changed_at');
    }

    /**
     * @return HasMany<CategoryDeletionRequest, $this>
     */
    public function deletionRequests(): HasMany
    {
        return $this->hasMany(CategoryDeletionRequest::class, 'category_id')->orderByDesc('requested_at');
    }

    /**
     * @return HasOne<CategoryDeletionRequest, $this>
     */
    public function pendingDeletionRequest(): HasOne
    {
        return $this->hasOne(CategoryDeletionRequest::class, 'category_id')
            ->where('status', EntityDeletionRequestStatus::Pending)
            ->latestOfMany('requested_at');
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
}
