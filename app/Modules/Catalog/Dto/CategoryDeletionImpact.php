<?php

namespace App\Modules\Catalog\Dto;

final class CategoryDeletionImpact
{
    /**
     * @param  list<string>  $blockers
     * @param  list<string>  $warnings
     */
    public function __construct(
        public readonly int $directChildrenCount,
        public readonly int $descendantsCount,
        public readonly int $productsCount,
        public readonly int $redirectsCount,
        public readonly bool $hasAiContent,
        public readonly bool $canApprove,
        public readonly array $blockers,
        public readonly array $warnings,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'direct_children_count' => $this->directChildrenCount,
            'descendants_count' => $this->descendantsCount,
            'products_count' => $this->productsCount,
            'redirects_count' => $this->redirectsCount,
            'has_ai_content' => $this->hasAiContent,
            'can_approve' => $this->canApprove,
            'blockers' => $this->blockers,
            'warnings' => $this->warnings,
        ];
    }
}
