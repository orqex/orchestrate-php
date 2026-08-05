<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Enum;

/**
 * Font family applied to a hosted checkout page.
 *
 * The checkout `appearance.font` object carries the resolved value under its
 * `value` key; use these cases to match it type-safely.
 */
enum FontFamily: string
{
    case INTER = 'inter';
    case POPPINS = 'poppins';
    case ROBOTO = 'roboto';
    case OPEN_SANS = 'open_sans';
    case LATO = 'lato';
    case MONTSERRAT = 'montserrat';
    case SOURCE_SANS_PRO = 'source_sans_pro';
    case NUNITO = 'nunito';
    case RALEWAY = 'raleway';
    case WORK_SANS = 'work_sans';
    case DM_SANS = 'dm_sans';
    case PLUS_JAKARTA_SANS = 'plus_jakarta_sans';
    case SPACE_GROTESK = 'space_grotesk';

    public function label(): string
    {
        return match ($this) {
            self::INTER             => 'Inter',
            self::POPPINS           => 'Poppins',
            self::ROBOTO            => 'Roboto',
            self::OPEN_SANS         => 'Open Sans',
            self::LATO              => 'Lato',
            self::MONTSERRAT        => 'Montserrat',
            self::SOURCE_SANS_PRO   => 'Source Sans Pro',
            self::NUNITO            => 'Nunito',
            self::RALEWAY           => 'Raleway',
            self::WORK_SANS         => 'Work Sans',
            self::DM_SANS           => 'DM Sans',
            self::PLUS_JAKARTA_SANS => 'Plus Jakarta Sans',
            self::SPACE_GROTESK     => 'Space Grotesk',
        };
    }
}
