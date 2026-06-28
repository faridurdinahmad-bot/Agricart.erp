<?php

use App\Core\Numbering\EntityCodeGenerator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $maxCategoryNumber = 0;

        DB::table('catalog_categories')
            ->where('code', 'like', 'CAT-%')
            ->orderBy('id')
            ->get(['id', 'code'])
            ->each(function (object $category) use (&$maxCategoryNumber): void {
                $numeric = EntityCodeGenerator::parseNumericSuffix((string) $category->code, 'CAT');

                if ($numeric === null) {
                    return;
                }

                $maxCategoryNumber = max($maxCategoryNumber, $numeric);

                $normalized = EntityCodeGenerator::format('CAT', $numeric);

                if ($normalized !== $category->code) {
                    DB::table('catalog_categories')
                        ->where('id', $category->id)
                        ->update(['code' => $normalized]);
                }
            });

        EntityCodeGenerator::bootstrapSequence('CAT', $maxCategoryNumber);

        foreach (['BR', 'PRD', 'SUP', 'CUS'] as $prefix) {
            EntityCodeGenerator::bootstrapSequence($prefix, 0);
        }
    }

    public function down(): void
    {
        DB::table('entity_code_sequences')->truncate();
    }
};
