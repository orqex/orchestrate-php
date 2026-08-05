<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Tests\Service;

use Orqex\Orchestrate\Enum\RefundReason;
use Orqex\Orchestrate\Resource\Amount;
use Orqex\Orchestrate\Resource\Currency;
use Orqex\Orchestrate\Resource\ExchangeRate;
use Orqex\Orchestrate\Resource\ExchangeRateDetails;
use Orqex\Orchestrate\Resource\Refund;
use Orqex\Orchestrate\Tests\Support\FakeApi;
use PHPUnit\Framework\TestCase;

final class RefundAndExchangeRateServiceTest extends TestCase
{
    public function test_refund_create_uses_the_nested_path(): void
    {
        $api = new FakeApi([FakeApi::json([
            'data' => [
                'id'               => 're_1',
                'amount'           => ['value' => 50, 'currency' => 'USD'],
                'processed_amount' => ['value' => 46, 'currency' => 'EUR'],
                'exchange_rate'    => [
                    'value'         => '0.92000000',
                    'from_currency' => 'USD',
                    'to_currency'   => 'EUR',
                    'expression'    => '$1.00 ≈ €0.92',
                ],
                'status'           => 'pending',
                'reason'           => ['value' => 'duplicate', 'label' => 'Duplicate payment'],
                'note'             => 'Charged twice',
            ],
        ], 201)]);

        $refund = $api->client->refunds()->create('TRX1', [
            'amount' => 50,
            'reason' => RefundReason::DUPLICATE->value,
            'note'   => 'Charged twice',
        ]);

        $this->assertInstanceOf(Refund::class, $refund);
        $this->assertSame('re_1', $refund->id);
        $this->assertSame(50, $refund->amount->value);
        $this->assertInstanceOf(Amount::class, $refund->processed_amount);
        $this->assertSame(46, $refund->processed_amount->value);
        $this->assertInstanceOf(ExchangeRateDetails::class, $refund->exchange_rate);
        $this->assertSame('0.92000000', $refund->exchange_rate->value);
        $this->assertSame('duplicate', $refund->reason['value']);
        $this->assertSame('Duplicate payment', $refund->reason['label']);
        $this->assertSame('Charged twice', $refund->note);
        $this->assertSame('POST', $api->lastRequest()->getMethod());
        $this->assertSame('/v1/payment/intents/TRX1/refunds', $api->lastRequest()->getUri()->getPath());
    }

    public function test_refund_retrieve_uses_the_nested_path(): void
    {
        $api = new FakeApi([FakeApi::json(['data' => ['id' => 're_1']])]);

        $api->client->refunds()->retrieve('TRX1', 're_1');

        $this->assertSame('/v1/payment/intents/TRX1/refunds/re_1', $api->lastRequest()->getUri()->getPath());
    }

    public function test_exchange_rate_latest_returns_rates(): void
    {
        $api = new FakeApi([FakeApi::json([
            'data' => ['base' => 'USD', 'date' => '2026-06-08 12:00:00', 'rates' => ['USD' => 600.5, 'EUR' => 0.92]],
        ])]);

        $rate = $api->client->exchangeRates()->latest('USD');

        $this->assertInstanceOf(ExchangeRate::class, $rate);
        $this->assertSame('USD', $rate->base);
        $this->assertSame(600.5, $rate->rateFor('USD'));
        $this->assertNull($rate->rateFor('JPY'));
        $this->assertSame('/v1/exchange-rates/latest/USD', $api->lastRequest()->getUri()->getPath());
    }

    public function test_supported_currencies_returns_a_typed_list(): void
    {
        $api = new FakeApi([FakeApi::json([
            'data' => [
                ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$'],
                ['code' => 'USD', 'name' => 'West African CFA franc', 'symbol' => 'CFA'],
            ],
            'meta' => ['total' => 2],
        ])]);

        $currencies = $api->client->exchangeRates()->supportedCurrencies();

        $this->assertCount(2, $currencies);
        $this->assertContainsOnlyInstancesOf(Currency::class, $currencies);
        $this->assertSame('USD', $currencies[0]->code);
    }
}
