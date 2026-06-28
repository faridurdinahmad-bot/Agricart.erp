<?php

namespace Tests\Unit;

use App\Core\Numbering\EntityCodeGenerator;
use App\Models\Catalog\Category;
use App\Models\EntityCodeSequence;
use App\Modules\Catalog\Services\CategoryCodeGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EntityCodeGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_issues_simple_sequential_codes(): void
    {
        $this->assertSame('CAT-1', EntityCodeGenerator::next('CAT'));
        $this->assertSame('CAT-2', EntityCodeGenerator::next('CAT'));
        $this->assertSame('BR-1', EntityCodeGenerator::next('BR'));
    }

    public function test_it_never_reuses_numbers_after_deletion(): void
    {
        Category::query()->create([
            'code' => EntityCodeGenerator::next('CAT'),
            'name_en' => 'Irrigation',
            'name_ur' => 'آبپاشی',
            'is_active' => true,
        ]);

        Category::query()->delete();

        $this->assertSame('CAT-2', EntityCodeGenerator::next('CAT'));
    }

    public function test_category_code_generator_uses_configured_prefix(): void
    {
        $this->assertSame('CAT-1', CategoryCodeGenerator::next());
        $this->assertSame('CAT-2', CategoryCodeGenerator::next());
    }

    public function test_it_formats_and_parses_codes(): void
    {
        $this->assertSame('PRD-45821', EntityCodeGenerator::format('PRD', 45821));
        $this->assertSame(45821, EntityCodeGenerator::parseNumericSuffix('PRD-45821', 'PRD'));
        $this->assertSame(87, EntityCodeGenerator::parseNumericSuffix('BR-87', 'BR'));
        $this->assertNull(EntityCodeGenerator::parseNumericSuffix('CAT-ABC', 'CAT'));
    }

    public function test_bootstrap_sequence_starts_from_existing_maximum(): void
    {
        EntityCodeGenerator::bootstrapSequence('CAT', 1254);

        $this->assertSame('CAT-1255', EntityCodeGenerator::next('CAT'));
        $this->assertSame(1255, EntityCodeSequence::query()->find('CAT')?->last_number);
    }
}
