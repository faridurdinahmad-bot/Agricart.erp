<?php

namespace App\Core\Numbering;

use App\Models\EntityCodeSequence;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class EntityCodeGenerator
{
    /**
     * Issue the next monotonic code for the given prefix.
     *
     * Numbers are never reused, even when earlier records are deleted.
     */
    public static function next(string $prefix): string
    {
        $prefix = self::normalizePrefix($prefix);

        return DB::transaction(function () use ($prefix): string {
            $sequence = EntityCodeSequence::query()
                ->where('prefix', $prefix)
                ->lockForUpdate()
                ->first();

            if ($sequence === null) {
                $sequence = EntityCodeSequence::query()->create([
                    'prefix' => $prefix,
                    'last_number' => 0,
                ]);
            }

            $nextNumber = $sequence->last_number + 1;

            $sequence->update([
                'last_number' => $nextNumber,
            ]);

            return self::format($prefix, $nextNumber);
        });
    }

    public static function format(string $prefix, int $number): string
    {
        $prefix = self::normalizePrefix($prefix);

        if ($number < 1) {
            throw new InvalidArgumentException('Entity code numbers must be greater than zero.');
        }

        return $prefix.'-'.$number;
    }

    public static function parseNumericSuffix(string $code, string $prefix): ?int
    {
        $prefix = self::normalizePrefix($prefix);
        $pattern = '/^'.preg_quote($prefix, '/').'-(\d+)$/';

        if (preg_match($pattern, $code, $matches) !== 1) {
            return null;
        }

        return (int) $matches[1];
    }

    public static function bootstrapSequence(string $prefix, int $lastNumber): void
    {
        $prefix = self::normalizePrefix($prefix);

        if ($lastNumber < 0) {
            throw new InvalidArgumentException('Sequence bootstrap value cannot be negative.');
        }

        EntityCodeSequence::query()->updateOrCreate(
            ['prefix' => $prefix],
            ['last_number' => $lastNumber],
        );
    }

    public static function prefixFor(string $entityKey): string
    {
        $prefix = config('entity_codes.prefixes.'.$entityKey);

        if (! is_string($prefix) || $prefix === '') {
            throw new InvalidArgumentException("Unknown entity code key [{$entityKey}].");
        }

        return self::normalizePrefix($prefix);
    }

    private static function normalizePrefix(string $prefix): string
    {
        $prefix = strtoupper(trim($prefix));

        if (! preg_match('/^[A-Z]{2,5}$/', $prefix)) {
            throw new InvalidArgumentException("Invalid entity code prefix [{$prefix}].");
        }

        return $prefix;
    }
}
