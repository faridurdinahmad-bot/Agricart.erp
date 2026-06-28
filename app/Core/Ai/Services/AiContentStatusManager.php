<?php

namespace App\Core\Ai\Services;

use App\Core\Ai\Enums\AiContentStatus;
use Illuminate\Database\Eloquent\Model;

final class AiContentStatusManager
{
    public static function markPending(Model $record): void
    {
        self::updateStatus($record, AiContentStatus::AiPending);
    }

    public static function markComplete(Model $record): void
    {
        self::updateStatus($record, AiContentStatus::Complete);
    }

    public static function markFailed(Model $record): void
    {
        self::updateStatus($record, AiContentStatus::AiFailed);
    }

    public static function markNeedsReview(Model $record): void
    {
        self::updateStatus($record, AiContentStatus::NeedsReview);
    }

    /**
     * Business saves should never be blocked by AI availability.
     * Call this after a successful manual save when AI content is still outstanding.
     */
    public static function markPendingIfAiFieldsEmpty(Model $record, array $aiFieldKeys): void
    {
        $hasAiContent = collect($aiFieldKeys)
            ->contains(fn (string $key): bool => filled($record->getAttribute($key)));

        self::updateStatus(
            $record,
            $hasAiContent ? AiContentStatus::NeedsReview : AiContentStatus::AiPending,
        );
    }

    protected static function updateStatus(Model $record, AiContentStatus $status): void
    {
        if (! self::supportsAiContentStatus($record)) {
            return;
        }

        $record->forceFill(['ai_content_status' => $status])->save();
    }

    protected static function supportsAiContentStatus(Model $record): bool
    {
        return in_array('ai_content_status', $record->getFillable(), true)
            || array_key_exists('ai_content_status', $record->getAttributes())
            || $record->isFillable('ai_content_status');
    }
}
