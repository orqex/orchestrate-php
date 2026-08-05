<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Resource;

/**
 * Latest exchange rates for a base currency.
 *
 * @property string $base Base currency code (e.g. "USD").
 * @property string $date Timestamp the rates were captured.
 * @property array<string,float> $rates Map of currency code to rate against the base.
 */
final class ExchangeRate extends BaseResource
{
    public function rateFor(string $currency): ?float
    {
        $rates = $this->attributes['rates'] ?? [];

        return isset($rates[$currency]) ? (float) $rates[$currency] : null;
    }
}
