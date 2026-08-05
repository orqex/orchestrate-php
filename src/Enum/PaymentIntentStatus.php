<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Enum;

/**
 * Lifecycle status of a payment intent.
 */
enum PaymentIntentStatus: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case EXPIRED = 'expired';
    case REFUNDED = 'refunded';
    case PARTIALLY_REFUNDED = 'partially_refunded';

    public function label(): string
    {
        return match ($this) {
            self::PENDING            => 'Pending',
            self::COMPLETED          => 'Completed',
            self::FAILED             => 'Failed',
            self::EXPIRED            => 'Expired',
            self::REFUNDED           => 'Refunded',
            self::PARTIALLY_REFUNDED => 'Partially refunded',
        };
    }

    public function isFinal(): bool
    {
        return $this !== self::PENDING;
    }

    public function isSuccessful(): bool
    {
        return $this === self::COMPLETED;
    }
}
