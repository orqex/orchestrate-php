<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Enum;

/**
 * Visual template applied to a hosted checkout page.
 */
enum CheckoutTemplate: string
{
    case RAVEN = 'raven';
    case MAGPIE = 'magpie';
    case ROOK = 'rook';

    public function label(): string
    {
        return match ($this) {
            self::RAVEN  => 'Raven',
            self::MAGPIE => 'Magpie',
            self::ROOK   => 'Rook',
        };
    }
}
