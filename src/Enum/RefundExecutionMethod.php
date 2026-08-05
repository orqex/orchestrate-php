<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Enum;

/**
 * How a refund was executed: through the gateway's native refund, or as an
 * outbound payout back to the original payer.
 */
enum RefundExecutionMethod: string
{
    case GATEWAY = 'gateway';
    case PAYOUT = 'payout';

    public function label(): string
    {
        return match ($this) {
            self::GATEWAY => 'Gateway',
            self::PAYOUT  => 'Payout',
        };
    }
}
