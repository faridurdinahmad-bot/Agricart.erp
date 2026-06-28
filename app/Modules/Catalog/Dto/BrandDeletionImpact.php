<?php

namespace App\Modules\Catalog\Dto;

final readonly class BrandDeletionImpact
{
    /**
     * @param  list<string>  $blockers
     * @param  list<string>  $warnings
     */
    public function __construct(
        public int $assignedCategoriesCount,
        public int $productsCount,
        public bool $hasAiContent,
        public bool $canApprove,
        public array $blockers,
        public array $warnings,
    ) {}
}
