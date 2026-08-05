<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Enum;

/**
 * Status of a payment requery run.
 */
enum RequeryStatus: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING   => 'Pending',
            self::COMPLETED => 'Completed',
            self::FAILED    => 'Failed',
        };
    }
}
