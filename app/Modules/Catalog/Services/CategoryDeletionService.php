<?php

namespace App\Modules\Catalog\Services;

use App\Core\Deletion\Enums\EntityDeletionRequestStatus;
use App\Models\Catalog\Category;
use App\Models\Catalog\CategoryDeletionRequest;
use App\Models\User;
use App\Modules\Catalog\Dto\CategoryDeletionImpact;
use App\Modules\Catalog\Enums\CategoryLifecycleStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class CategoryDeletionService
{
    public static function requestDeletion(Category $category, User $requestedBy, ?string $reason = null): CategoryDeletionRequest
    {
        if ($category->trashed()) {
            throw ValidationException::withMessages([
                'category' => 'This category has already been deleted.',
            ]);
        }

        if ($category->isPendingDeletion()) {
            throw ValidationException::withMessages([
                'category' => 'A deletion request is already pending for this category.',
            ]);
        }

        $reason = trim((string) ($reason ?? ''));

        if ($reason === '') {
            throw ValidationException::withMessages([
                'deletionReason' => 'A deletion reason is required before submitting for approval.',
            ]);
        }

        return DB::transaction(function () use ($category, $requestedBy, $reason): CategoryDeletionRequest {
            $category->update([
                'lifecycle_status' => CategoryLifecycleStatus::PendingDeletion,
                'is_active' => false,
            ]);

            return CategoryDeletionRequest::query()->create([
                'category_id' => $category->id,
                'status' => EntityDeletionRequestStatus::Pending,
                'reason' => $reason,
                'requested_by' => $requestedBy->id,
                'requested_at' => now(),
            ]);
        });
    }

    public static function approveDeletion(CategoryDeletionRequest $request, User $reviewer): Category
    {
        if (! $request->isPending()) {
            throw ValidationException::withMessages([
                'request' => 'This deletion request is no longer pending.',
            ]);
        }

        $category = $request->category()->firstOrFail();
        $impact = CategoryDeletionImpactAnalyzer::analyze($category);

        if (! $impact->canApprove) {
            throw ValidationException::withMessages([
                'request' => implode(' ', $impact->blockers),
            ]);
        }

        return DB::transaction(function () use ($request, $reviewer, $category): Category {
            $request->update([
                'status' => EntityDeletionRequestStatus::Approved,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
            ]);

            $category->update([
                'lifecycle_status' => CategoryLifecycleStatus::Deleted,
                'is_active' => false,
            ]);

            $category->delete();

            return $category;
        });
    }

    public static function rejectDeletion(
        CategoryDeletionRequest $request,
        User $reviewer,
        string $reviewNotes,
    ): Category {
        if (! $request->isPending()) {
            throw ValidationException::withMessages([
                'request' => 'This deletion request is no longer pending.',
            ]);
        }

        $reviewNotes = trim($reviewNotes);

        if ($reviewNotes === '') {
            throw ValidationException::withMessages([
                'reviewNotes' => 'Rejection reason is required.',
            ]);
        }

        return DB::transaction(function () use ($request, $reviewer, $reviewNotes): Category {
            $category = $request->category()->firstOrFail();

            $request->update([
                'status' => EntityDeletionRequestStatus::Rejected,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'review_notes' => $reviewNotes,
            ]);

            $category->update([
                'lifecycle_status' => CategoryLifecycleStatus::Active,
                'is_active' => true,
            ]);

            return $category->refresh();
        });
    }

    public static function returnDeletion(
        CategoryDeletionRequest $request,
        User $reviewer,
        string $returnNotes,
    ): Category {
        if (! $request->isPending()) {
            throw ValidationException::withMessages([
                'request' => 'This deletion request is no longer pending.',
            ]);
        }

        $returnNotes = trim($returnNotes);

        if ($returnNotes === '') {
            throw ValidationException::withMessages([
                'returnNotes' => 'Return reason is required.',
            ]);
        }

        return DB::transaction(function () use ($request, $reviewer, $returnNotes): Category {
            $category = $request->category()->firstOrFail();

            $request->update([
                'status' => EntityDeletionRequestStatus::Returned,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'review_notes' => $returnNotes,
            ]);

            $category->update([
                'lifecycle_status' => CategoryLifecycleStatus::Active,
                'is_active' => true,
            ]);

            return $category->refresh();
        });
    }

    public static function impactFor(Category $category): CategoryDeletionImpact
    {
        return CategoryDeletionImpactAnalyzer::analyze($category);
    }
}
