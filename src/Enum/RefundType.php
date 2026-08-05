<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Enum;

/**
 * Whether a refund covers the full payment or only part of it.
 */
enum RefundType: string
{
    case FULL = 'full';
    case PARTIAL = 'partial';

    public function label(): string
    {
        return match ($this) {
            self::FULL    => 'Full',
            self::PARTIAL => 'Partial',
        };
    }
}
