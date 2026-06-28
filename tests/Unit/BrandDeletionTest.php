<?php

namespace Tests\Unit;

use App\Core\Deletion\Enums\EntityDeletionRequestStatus;
use App\Models\Catalog\Brand;
use App\Models\Catalog\BrandDeletionRequest;
use App\Models\User;
use App\Modules\Catalog\Enums\BrandLifecycleStatus;
use App\Modules\Catalog\Services\BrandDeletionService;
use App\Modules\Catalog\Services\BrandManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BrandDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_request_marks_brand_pending_deletion_without_removing_it(): void
    {
        $user = User::factory()->create();

        $brand = Brand::query()->create([
            'code' => 'BR-1',
            'name_en' => 'Kubota',
            'name_ur' => 'کوبوتا',
            'is_active' => true,
            'lifecycle_status' => BrandLifecycleStatus::Active,
        ]);

        $request = BrandManager::requestDeletion($brand, $user, 'Outdated brand');

        $brand->refresh();
        $request->refresh();

        $this->assertTrue($brand->isPendingDeletion());
        $this->assertFalse($brand->is_active);
        $this->assertNull($brand->deleted_at);
        $this->assertSame(EntityDeletionRequestStatus::Pending, $request->status);
        $this->assertSame('Outdated brand', $request->reason);
        $this->assertSame(1, Brand::query()->count());
    }

    public function test_approve_soft_deletes_brand(): void
    {
        $requester = User::factory()->create();
        $approver = User::factory()->create();

        $brand = Brand::query()->create([
            'code' => 'BR-2',
            'name_en' => 'NSK',
            'name_ur' => 'این ایس کے',
            'is_active' => true,
            'lifecycle_status' => BrandLifecycleStatus::Active,
        ]);

        $request = BrandManager::requestDeletion($brand, $requester, 'No longer needed');

        BrandDeletionService::approveDeletion($request, $approver);

        $this->assertSoftDeleted('catalog_brands', ['id' => $brand->id]);
        $this->assertSame(
            EntityDeletionRequestStatus::Approved,
            BrandDeletionRequest::query()->find($request->id)?->status,
        );
    }

    public function test_reject_restores_brand_to_active(): void
    {
        $requester = User::factory()->create();
        $approver = User::factory()->create();

        $brand = Brand::query()->create([
            'code' => 'BR-3',
            'name_en' => 'Rain Bird',
            'name_ur' => 'رین برڈ',
            'is_active' => true,
            'lifecycle_status' => BrandLifecycleStatus::Active,
        ]);

        $request = BrandManager::requestDeletion($brand, $requester, 'Mistake');

        BrandDeletionService::rejectDeletion($request, $approver, 'Keep this brand');

        $brand->refresh();

        $this->assertFalse($brand->isPendingDeletion());
        $this->assertTrue($brand->is_active);
        $this->assertSame(BrandLifecycleStatus::Active, $brand->lifecycle_status);
    }

    public function test_reject_restores_inactive_brand_when_it_was_inactive_before_request(): void
    {
        $requester = User::factory()->create();
        $approver = User::factory()->create();

        $brand = Brand::query()->create([
            'code' => 'BR-5',
            'name_en' => 'Inactive Brand',
            'name_ur' => 'غیر فعال',
            'is_active' => false,
            'lifecycle_status' => BrandLifecycleStatus::Active,
        ]);

        $request = BrandManager::requestDeletion($brand, $requester, 'Cleanup inactive brand');

        BrandDeletionService::rejectDeletion($request, $approver, 'Keep inactive record');

        $brand->refresh();

        $this->assertFalse($brand->isPendingDeletion());
        $this->assertFalse($brand->is_active);
        $this->assertSame(BrandLifecycleStatus::Active, $brand->lifecycle_status);
    }

    public function test_return_restores_brand_with_notes(): void
    {
        $requester = User::factory()->create();
        $approver = User::factory()->create();

        $brand = Brand::query()->create([
            'code' => 'BR-6',
            'name_en' => 'Return Me',
            'name_ur' => 'واپس',
            'is_active' => true,
            'lifecycle_status' => BrandLifecycleStatus::Active,
        ]);

        $request = BrandManager::requestDeletion($brand, $requester, 'Wrong request');

        BrandDeletionService::returnDeletion($request, $approver, 'Please review brand assignments first');

        $brand->refresh();
        $request->refresh();

        $this->assertFalse($brand->isPendingDeletion());
        $this->assertTrue($brand->is_active);
        $this->assertSame(EntityDeletionRequestStatus::Returned, $request->status);
        $this->assertSame('Please review brand assignments first', $request->review_notes);
    }

    public function test_direct_delete_is_not_allowed(): void
    {
        $brand = Brand::query()->create([
            'code' => 'BR-7',
            'name_en' => 'Protected',
            'name_ur' => 'محفوظ',
            'is_active' => true,
            'lifecycle_status' => BrandLifecycleStatus::Active,
        ]);

        $this->expectException(ValidationException::class);

        BrandManager::delete($brand);
    }

    public function test_deletion_requires_reason(): void
    {
        $user = User::factory()->create();

        $brand = Brand::query()->create([
            'code' => 'BR-4',
            'name_en' => 'Bahco',
            'name_ur' => 'باہکو',
            'is_active' => true,
            'lifecycle_status' => BrandLifecycleStatus::Active,
        ]);

        $this->expectException(ValidationException::class);

        BrandManager::requestDeletion($brand, $user, '');
    }
}
