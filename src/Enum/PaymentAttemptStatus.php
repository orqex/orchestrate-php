<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Enum;

/**
 * Lifecycle status of a payment attempt.
 */
enum PaymentAttemptStatus: string
{
    case PROCESSING = 'processing';
    case ACTION_REQUIRED = 'action_required';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PROCESSING      => 'Processing',
            self::ACTION_REQUIRED => 'Action required',
            self::COMPLETED       => 'Completed',
            self::FAILED          => 'Failed',
            self::CANCELLED       => 'Cancelled',
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::COMPLETED, self::FAILED, self::CANCELLED], true);
    }

    public function requiresAction(): bool
    {
        return $this === self::ACTION_REQUIRED;
    }
}
