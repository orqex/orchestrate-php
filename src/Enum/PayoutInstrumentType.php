<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Enum;

/**
 * Typed destination for a payout. Selects which `instrument.*` fields are
 * required when creating a payout.
 */
enum PayoutInstrumentType: string
{
    case PHONE = 'phone';
    case BANK_ACCOUNT = 'bank_account';
    case CRYPTO_ADDRESS = 'crypto_address';

    public function label(): string
    {
        return match ($this) {
            self::PHONE          => 'Phone',
            self::BANK_ACCOUNT   => 'Bank account',
            self::CRYPTO_ADDRESS => 'Crypto address',
        };
    }
}
