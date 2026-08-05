<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Enum;

/**
 * Border style applied to checkout UI elements.
 */
enum BorderStyle: string
{
    case ROUNDED = 'rounded';
    case SQUARE = 'square';

    public function label(): string
    {
        return match ($this) {
            self::ROUNDED => 'Rounded',
            self::SQUARE  => 'Square',
        };
    }
}
