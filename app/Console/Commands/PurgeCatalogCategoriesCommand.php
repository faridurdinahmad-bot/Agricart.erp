<?php

namespace App\Console\Commands;

use App\Core\Numbering\EntityCodeGenerator;
use App\Models\Catalog\Category;
use App\Models\Catalog\CategoryDeletionRequest;
use App\Models\Catalog\CategoryUrlRedirect;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PurgeCatalogCategoriesCommand extends Command
{
    protected $signature = 'agricart:purge-catalog-categories {--force : Confirm destructive purge of all category data}';

    protected $description = 'Remove all category records, related data, uploaded images, and reset the CAT code sequence';

    public function handle(): int
    {
        if (! $this->option('force')) {
            $this->error('This command permanently removes all catalog category data.');
            $this->line('Re-run with --force to confirm.');

            return self::FAILURE;
        }

        $categoryCount = Category::withTrashed()->count();
        $redirectCount = CategoryUrlRedirect::query()->count();
        $requestCount = CategoryDeletionRequest::query()->count();

        DB::transaction(function (): void {
            CategoryDeletionRequest::query()->delete();
            CategoryUrlRedirect::query()->delete();
            Category::withTrashed()->forceDelete();
            EntityCodeGenerator::bootstrapSequence('CAT', 0);
        });

        if (Storage::disk('public')->exists('catalog/categories')) {
            Storage::disk('public')->deleteDirectory('catalog/categories');
        }

        $this->info('Catalog category purge complete.');
        $this->table(
            ['Removed', 'Count'],
            [
                ['Categories (including soft-deleted)', (string) $categoryCount],
                ['URL redirects', (string) $redirectCount],
                ['Deletion requests', (string) $requestCount],
                ['CAT sequence reset to', '0 (next code: CAT-1)'],
            ],
        );

        return self::SUCCESS;
    }
}
