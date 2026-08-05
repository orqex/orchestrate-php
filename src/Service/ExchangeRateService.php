<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Service;

use Orqex\Orchestrate\Http\RequestOptions;
use Orqex\Orchestrate\Resource\Currency;
use Orqex\Orchestrate\Resource\ExchangeRate;

/**
 * Read-only exchange-rate catalogue.
 */
final class ExchangeRateService extends AbstractService
{
    /**
     * Latest exchange rates for a base currency (defaults to USD).
     *
     * @param null|array<string,mixed>|RequestOptions $opts
     */
    public function latest(string $baseCurrency = 'USD', null|array|RequestOptions $opts = null): ExchangeRate
    {
        return $this->requestResource(
            ExchangeRate::class,
            'GET',
            $this->buildPath('/exchange-rates/latest/%s', $baseCurrency),
            [],
            $opts,
        );
    }

    /**
     * List the currencies supported by the exchange-rate service.
     *
     * @param null|array<string,mixed>|RequestOptions $opts
     *
     * @return array<int, Currency>
     */
    public function supportedCurrencies(null|array|RequestOptions $opts = null): array
    {
        $response = $this->requestRaw('GET', '/exchange-rates/currencies', [], $opts);
        $rows = is_array($response->json['data'] ?? null) ? $response->json['data'] : [];

        return array_values(array_map(
            static fn (array $row): Currency => Currency::constructFrom($row, $response),
            array_filter($rows, 'is_array'),
        ));
    }
}
