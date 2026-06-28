<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

use App\Core\Ai\Dto\AiTaskRequest;
use App\Core\Ai\Enums\AiTargetModule;
use App\Core\Ai\Enums\AiTaskType;
use App\Core\Ai\Support\AiTaskPromptRegistry;
use App\Models\Ai\AiPromptTemplate;
use Illuminate\Contracts\Console\Kernel;

$count = AiPromptTemplate::query()->count();
echo "Templates in DB: {$count}\n";

$resolved = AiTaskPromptRegistry::build(
    AiTaskRequest::make(
        taskType: AiTaskType::CategoryContent,
        targetModule: AiTargetModule::Catalog,
        context: ['name_en' => 'Irrigation Pumps', 'hs_code' => '8424.81'],
    ),
);

$ok = str_contains($resolved->user, 'Irrigation Pumps')
    && str_contains($resolved->user, '8424.81')
    && str_contains($resolved->system, 'JSON');

echo str_contains($resolved->user, 'Irrigation Pumps') ? "Category prompt: PASS\n" : "Category prompt: FAIL\n";
echo $resolved->jsonResponse ? "JSON mode: PASS\n" : "JSON mode: FAIL\n";

exit($ok && $count >= 7 ? 0 : 1);
