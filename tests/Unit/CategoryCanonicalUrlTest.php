<?php

namespace Tests\Unit;

use App\Models\Catalog\Category;
use App\Models\Catalog\CategoryUrlRedirect;
use App\Models\User;
use App\Modules\Catalog\Services\CategoryCanonicalUrlService;
use App\Modules\Catalog\Services\CategoryManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryCanonicalUrlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'catalog.storefront_base_url' => 'https://agricart.pk',
            'catalog.category_path_prefix' => 'category',
        ]);
    }

    public function test_create_generates_slug_and_canonical_url(): void
    {
        $parent = Category::query()->create([
            'code' => 'CAT-000010',
            'name_en' => 'Irrigation',
            'name_ur' => 'آبپاشی',
            'url_slug' => 'irrigation',
            'canonical_url' => 'https://agricart.pk/category/irrigation',
            'is_active' => true,
        ]);

        $category = CategoryManager::create([
            'parent_id' => $parent->id,
            'name_en' => 'Sprayers',
            'name_ur' => 'مسنگل',
            'display_order' => 0,
            'is_active' => true,
        ]);

        $this->assertSame('sprayers', $category->url_slug);
        $this->assertSame('https://agricart.pk/category/irrigation/sprayers', $category->canonical_url);
    }

    public function test_seo_only_update_keeps_canonical_url(): void
    {
        $category = Category::query()->create([
            'code' => 'CAT-000011',
            'name_en' => 'Sprayers',
            'name_ur' => 'مسنگل',
            'url_slug' => 'sprayers',
            'canonical_url' => 'https://agricart.pk/category/sprayers',
            'is_active' => true,
        ]);

        $updated = CategoryManager::update($category, [
            'parent_id' => null,
            'name_en' => 'Sprayers',
            'name_ur' => 'مسنگل',
            'display_order' => 0,
            'is_active' => true,
            'seo_title' => 'Buy Sprayers Online',
        ], null, User::factory()->create());

        $this->assertSame('https://agricart.pk/category/sprayers', $updated->canonical_url);
        $this->assertSame(0, CategoryUrlRedirect::query()->count());
    }

    public function test_rename_records_301_redirect_and_updates_canonical(): void
    {
        $user = User::factory()->create();

        $category = Category::query()->create([
            'code' => 'CAT-000012',
            'name_en' => 'Sprayers',
            'name_ur' => 'مسنگل',
            'url_slug' => 'sprayers',
            'canonical_url' => 'https://agricart.pk/category/sprayers',
            'is_active' => true,
        ]);

        $updated = CategoryManager::update($category, [
            'parent_id' => null,
            'name_en' => 'Knapsack Sprayers',
            'name_ur' => 'مسنگل',
            'display_order' => 0,
            'is_active' => true,
        ], null, $user);

        $this->assertSame('knapsack-sprayers', $updated->url_slug);
        $this->assertSame('https://agricart.pk/category/knapsack-sprayers', $updated->canonical_url);

        $redirect = CategoryUrlRedirect::query()->first();
        $this->assertNotNull($redirect);
        $this->assertSame('https://agricart.pk/category/sprayers', $redirect->old_url);
        $this->assertSame('https://agricart.pk/category/knapsack-sprayers', $redirect->new_url);
        $this->assertSame(301, $redirect->redirect_status);
        $this->assertSame($user->id, $redirect->changed_by);
    }

    public function test_compose_canonical_url_matches_expected_pattern(): void
    {
        $url = CategoryCanonicalUrlService::composeCanonicalUrl(['irrigation', 'sprayers']);

        $this->assertSame('https://agricart.pk/category/irrigation/sprayers', $url);
    }
}
