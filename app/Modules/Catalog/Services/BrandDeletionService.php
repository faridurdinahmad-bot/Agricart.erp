<?php

namespace App\Modules\Catalog\Services;

use App\Core\Deletion\Enums\EntityDeletionRequestStatus;
use App\Models\Catalog\Brand;
use App\Models\Catalog\BrandDeletionRequest;
use App\Models\User;
use App\Modules\Catalog\Dto\BrandDeletionImpact;
use App\Modules\Catalog\Enums\BrandLifecycleStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class BrandDeletionService
{
    public static function requestDeletion(Brand $brand, User $requestedBy, ?string $reason = null): BrandDeletionRequest
    {
        if ($brand->trashed()) {
            throw ValidationException::withMessages([
                'brand' => 'This brand has already been deleted.',
            ]);
        }

        if ($brand->isPendingDeletion()) {
            throw ValidationException::withMessages([
                'brand' => 'A deletion request is already pending for this brand.',
            ]);
        }

        $reason = trim((string) ($reason ?? ''));

        if ($reason === '') {
            throw ValidationException::withMessages([
                'deletionReason' => 'A deletion reason is required before submitting for approval.',
            ]);
        }

        return DB::transaction(function () use ($brand, $requestedBy, $reason): BrandDeletionRequest {
            $wasActive = (bool) $brand->is_active;

            $brand->update([
                'lifecycle_status' => BrandLifecycleStatus::PendingDeletion,
                'is_active' => false,
            ]);

            return BrandDeletionRequest::query()->create([
                'brand_id' => $brand->id,
                'status' => EntityDeletionRequestStatus::Pending,
                'reason' => $reason,
                'was_active_before_request' => $wasActive,
                'requested_by' => $requestedBy->id,
                'requested_at' => now(),
            ]);
        });
    }

    public static function approveDeletion(BrandDeletionRequest $request, User $reviewer): Brand
    {
        if (! $request->isPending()) {
            throw ValidationException::withMessages([
                'request' => 'This deletion request is no longer pending.',
            ]);
        }

        $brand = $request->brand()->firstOrFail();
        $impact = BrandDeletionImpactAnalyzer::analyze($brand);

        if (! $impact->canApprove) {
            throw ValidationException::withMessages([
                'request' => implode(' ', $impact->blockers),
            ]);
        }

        return DB::transaction(function () use ($request, $reviewer, $brand): Brand {
            $request->update([
                'status' => EntityDeletionRequestStatus::Approved,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
            ]);

            $brand->update([
                'lifecycle_status' => BrandLifecycleStatus::Deleted,
                'is_active' => false,
            ]);

            $brand->delete();

            return $brand;
        });
    }

    public static function rejectDeletion(
        BrandDeletionRequest $request,
        User $reviewer,
        string $reviewNotes,
    ): Brand {
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

        return DB::transaction(function () use ($request, $reviewer, $reviewNotes): Brand {
            $brand = $request->brand()->firstOrFail();

            $request->update([
                'status' => EntityDeletionRequestStatus::Rejected,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'review_notes' => $reviewNotes,
            ]);

            $brand->update([
                'lifecycle_status' => BrandLifecycleStatus::Active,
                'is_active' => (bool) $request->was_active_before_request,
            ]);

            return $brand->refresh();
        });
    }

    public static function returnDeletion(
        BrandDeletionRequest $request,
        User $reviewer,
        string $returnNotes,
    ): Brand {
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

        return DB::transaction(function () use ($request, $reviewer, $returnNotes): Brand {
            $brand = $request->brand()->firstOrFail();

            $request->update([
                'status' => EntityDeletionRequestStatus::Returned,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'review_notes' => $returnNotes,
            ]);

            $brand->update([
                'lifecycle_status' => BrandLifecycleStatus::Active,
                'is_active' => (bool) $request->was_active_before_request,
            ]);

            return $brand->refresh();
        });
    }

    public static function impactFor(Brand $brand): BrandDeletionImpact
    {
        return BrandDeletionImpactAnalyzer::analyze($brand);
    }
}
