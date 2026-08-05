<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Enum;

/**
 * Lifecycle status of a hosted checkout session.
 */
enum CheckoutStatus: string
{
    case OPEN = 'open';
    case COMPLETED = 'completed';
    case EXPIRED = 'expired';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::OPEN      => 'Open',
            self::COMPLETED => 'Completed',
            self::EXPIRED   => 'Expired',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function isFinal(): bool
    {
        return $this !== self::OPEN;
    }
}
