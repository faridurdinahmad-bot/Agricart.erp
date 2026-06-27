<?php

namespace App\Core\Filament\Concerns;

use Filament\Notifications\Notification;

trait HandlesCrudModal
{
    protected function completeModalSave(
        bool $addAnother = false,
        ?string $title = null,
        ?string $body = null,
        bool $refreshNavigation = false,
    ): void {
        if ($title) {
            $notification = Notification::make()->title($title)->success();

            if ($body) {
                $notification->body($body);
            }

            $notification->send();
        }

        if ($addAnother) {
            $this->halt();

            return;
        }

        $this->unmountAction();

        if ($refreshNavigation) {
            $this->redirect(static::getUrl(), navigate: false);
        }
    }
}
