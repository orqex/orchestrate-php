<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Enum;

/**
 * Operational health of a payment method in a country.
 */
enum PaymentMethodStatus: string
{
    case UNAVAILABLE = 'unavailable';
    case AVAILABLE = 'available';
    case OPERATIONAL = 'operational';
    case DEGRADED = 'degraded';
    case DOWN = 'down';

    public function label(): string
    {
        return match ($this) {
            self::UNAVAILABLE => 'Unavailable',
            self::AVAILABLE   => 'Available',
            self::OPERATIONAL => 'Operational',
            self::DEGRADED    => 'Degraded',
            self::DOWN        => 'Down',
        };
    }

    /**
     * Whether the method is currently worth offering. `degraded` stays usable
     * on purpose: the payment may still succeed.
     */
    public function isUsable(): bool
    {
        return in_array($this, [self::AVAILABLE, self::OPERATIONAL, self::DEGRADED], true);
    }
}
