<?php

namespace App\Core\Ai\Enums;

enum AiConnectionTestStatus: string
{
    case Connected = 'connected';
    case Disconnected = 'disconnected';

    public function label(): string
    {
        return match ($this) {
            self::Connected => 'Connected',
            self::Disconnected => 'Disconnected',
        };
    }

    public function listBadgeClass(): string
    {
        return match ($this) {
            self::Connected => 'agricart-users-list__badge--active',
            self::Disconnected => 'agricart-users-list__badge--inactive',
        };
    }
}
