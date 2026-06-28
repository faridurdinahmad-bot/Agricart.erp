<?php

namespace Tests\Unit;

use App\Core\Deletion\Enums\EntityDeletionRequestStatus;
use App\Models\Catalog\Category;
use App\Models\Catalog\CategoryDeletionRequest;
use App\Models\User;
use App\Modules\Catalog\Enums\CategoryLifecycleStatus;
use App\Modules\Catalog\Services\CategoryDeletionService;
use App\Modules\Catalog\Services\CategoryManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CategoryDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_request_marks_category_pending_deletion_without_removing_it(): void
    {
        $user = User::factory()->create();

        $category = Category::query()->create([
            'code' => 'CAT-1',
            'name_en' => 'Sprayers',
            'name_ur' => 'مسنگل',
            'is_active' => true,
            'lifecycle_status' => CategoryLifecycleStatus::Active,
        ]);

        $request = CategoryManager::requestDeletion($category, $user, 'Outdated category');

        $category->refresh();
        $request->refresh();

        $this->assertTrue($category->isPendingDeletion());
        $this->assertFalse($category->is_active);
        $this->assertNull($category->deleted_at);
        $this->assertSame(EntityDeletionRequestStatus::Pending, $request->status);
        $this->assertSame($user->id, $request->requested_by);
        $this->assertSame('Outdated category', $request->reason);
        $this->assertSame(1, Category::query()->count());
    }

    public function test_approve_soft_deletes_leaf_category(): void
    {
        $requester = User::factory()->create();
        $approver = User::factory()->create();

        $category = Category::query()->create([
            'code' => 'CAT-2',
            'name_en' => 'Leaf Category',
            'name_ur' => 'زمرہ',
            'is_active' => true,
            'lifecycle_status' => CategoryLifecycleStatus::Active,
        ]);

        $request = CategoryManager::requestDeletion($category, $requester, 'No longer needed');

        CategoryDeletionService::approveDeletion($request, $approver);

        $this->assertSoftDeleted('catalog_categories', ['id' => $category->id]);
        $this->assertSame(
            EntityDeletionRequestStatus::Approved,
            CategoryDeletionRequest::query()->find($request->id)?->status,
        );
    }

    public function test_approve_is_blocked_when_children_exist(): void
    {
        $requester = User::factory()->create();
        $approver = User::factory()->create();

        $parent = Category::query()->create([
            'code' => 'CAT-3',
            'name_en' => 'Parent',
            'name_ur' => 'والد',
            'is_active' => true,
            'lifecycle_status' => CategoryLifecycleStatus::Active,
        ]);

        Category::query()->create([
            'code' => 'CAT-4',
            'name_en' => 'Child',
            'name_ur' => 'بچہ',
            'parent_id' => $parent->id,
            'is_active' => true,
            'lifecycle_status' => CategoryLifecycleStatus::Active,
        ]);

        $request = CategoryManager::requestDeletion($parent, $requester, 'Remove parent');

        $this->expectException(ValidationException::class);

        CategoryDeletionService::approveDeletion($request, $approver);
    }

    public function test_reject_restores_active_status(): void
    {
        $requester = User::factory()->create();
        $approver = User::factory()->create();

        $category = Category::query()->create([
            'code' => 'CAT-5',
            'name_en' => 'Restore Me',
            'name_ur' => 'بحال',
            'is_active' => true,
            'lifecycle_status' => CategoryLifecycleStatus::Active,
        ]);

        $request = CategoryManager::requestDeletion($category, $requester, 'No longer needed');

        CategoryDeletionService::rejectDeletion($request, $approver, 'Keep for now');

        $category->refresh();
        $request->refresh();

        $this->assertFalse($category->isPendingDeletion());
        $this->assertTrue($category->is_active);
        $this->assertSame(CategoryLifecycleStatus::Active, $category->lifecycle_status);
        $this->assertSame(EntityDeletionRequestStatus::Rejected, $request->status);
        $this->assertSame('Keep for now', $request->review_notes);
    }

    public function test_request_requires_reason(): void
    {
        $user = User::factory()->create();

        $category = Category::query()->create([
            'code' => 'CAT-7',
            'name_en' => 'Needs Reason',
            'name_ur' => 'وجہ',
            'is_active' => true,
            'lifecycle_status' => CategoryLifecycleStatus::Active,
        ]);

        $this->expectException(ValidationException::class);

        CategoryManager::requestDeletion($category, $user, '   ');
    }

    public function test_return_restores_category_with_notes(): void
    {
        $requester = User::factory()->create();
        $approver = User::factory()->create();

        $category = Category::query()->create([
            'code' => 'CAT-8',
            'name_en' => 'Return Me',
            'name_ur' => 'واپس',
            'is_active' => true,
            'lifecycle_status' => CategoryLifecycleStatus::Active,
        ]);

        $request = CategoryManager::requestDeletion($category, $requester, 'Wrong request');

        CategoryDeletionService::returnDeletion($request, $approver, 'Please clarify hierarchy first');

        $category->refresh();
        $request->refresh();

        $this->assertFalse($category->isPendingDeletion());
        $this->assertTrue($category->is_active);
        $this->assertSame(EntityDeletionRequestStatus::Returned, $request->status);
        $this->assertSame('Please clarify hierarchy first', $request->review_notes);
    }

    public function test_direct_delete_is_not_allowed(): void
    {
        $category = Category::query()->create([
            'code' => 'CAT-6',
            'name_en' => 'Protected',
            'name_ur' => 'محفوظ',
            'is_active' => true,
            'lifecycle_status' => CategoryLifecycleStatus::Active,
        ]);

        $this->expectException(ValidationException::class);

        CategoryManager::delete($category);
    }
}
