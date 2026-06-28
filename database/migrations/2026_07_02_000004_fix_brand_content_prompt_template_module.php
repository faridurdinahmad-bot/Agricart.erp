<?php

use App\Core\Ai\Enums\AiTargetModule;
use App\Core\Ai\Enums\AiTaskType;
use App\Models\Ai\AiPromptTemplate;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $legacy = AiPromptTemplate::query()
            ->where('task_type', AiTaskType::BrandContent)
            ->where('target_module', AiTargetModule::Brands)
            ->first();

        $catalog = AiPromptTemplate::query()
            ->where('task_type', AiTaskType::BrandContent)
            ->where('target_module', AiTargetModule::Catalog)
            ->first();

        if ($catalog === null && $legacy !== null) {
            $legacy->update([
                'target_module' => AiTargetModule::Catalog,
                'is_active' => true,
            ]);

            return;
        }

        if ($legacy !== null) {
            $legacy->update(['is_active' => false]);
        }
    }

    public function down(): void
    {
        AiPromptTemplate::query()
            ->where('task_type', AiTaskType::BrandContent)
            ->where('target_module', AiTargetModule::Brands)
            ->update(['is_active' => true]);
    }
};
