<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

use App\Models\Catalog\Category;
use App\Modules\Catalog\Concerns\InteractsWithCategoryForm;
use App\Modules\Catalog\Services\CategoryManager;
use Illuminate\Contracts\Console\Kernel;

$helper = new class
{
    use InteractsWithCategoryForm;

    public function formFromCategory(Category $category): array
    {
        return self::categoryFormFromModel($category);
    }
};

$category = Category::query()->create([
    'code' => 'TEST-AI-UR-'.time(),
    'name_en' => 'Test Seeds',
    'name_ur' => 'placeholder',
    'is_active' => true,
    'ai_content_status' => 'ai_pending',
]);

CategoryManager::applyAiGeneratedContent($category, [
    'urdu_name' => 'بیج و تخم',
    'short_description_en' => 'English short',
    'short_description_ur' => 'اردو مختصر',
    'long_description_en' => 'Long en',
    'long_description_ur' => 'Long ur',
    'usage_en' => 'Usage en',
    'usage_ur' => 'Usage ur',
    'benefits_en' => 'Benefits en',
    'benefits_ur' => 'Benefits ur',
    'seo_title' => 'Seeds',
    'seo_focus_keyword_en' => 'seeds',
    'seo_focus_keyword_ur' => 'بیج',
    'meta_description' => 'Meta',
    'url_slug' => 'test-seeds',
], 'test-model');

$fresh = Category::query()->findOrFail($category->id);
$form = $helper->formFromCategory($fresh);

$ok = $fresh->name_ur === 'بیج و تخم'
    && $form['urdu_name'] === 'بیج و تخم'
    && $fresh->ai_content_status->value === 'complete';

echo $ok ? "PASS: Urdu name saved and reloads in edit form.\n" : "FAIL\n";
echo "DB name_ur: {$fresh->name_ur}\n";
echo "Form urdu_name: {$form['urdu_name']}\n";

$category->delete();

exit($ok ? 0 : 1);
