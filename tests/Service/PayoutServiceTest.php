<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Tests\Service;

use Orqex\Orchestrate\Resource\Country;
use Orqex\Orchestrate\Resource\Customer;
use Orqex\Orchestrate\Resource\Failure;
use Orqex\Orchestrate\Resource\FailureCode;
use Orqex\Orchestrate\Resource\Payout;
use Orqex\Orchestrate\Resource\PayoutInstrument;
use Orqex\Orchestrate\Resource\Refund;
use Orqex\Orchestrate\Resource\RefundPayout;
use Orqex\Orchestrate\Tests\Support\FakeApi;
use PHPUnit\Framework\TestCase;

final class PayoutServiceTest extends TestCase
{
    public function test_payout_create_sends_customer_description_reference_and_hydrates(): void
    {
        $api = new FakeApi([FakeApi::json(['data' => self::payoutFixture()], 201)]);

        $payout = $api->client->payouts()->create([
            'amount'      => 5000,
            'currency'    => 'USD',
            'method'      => 'test',
            'description' => 'Payout to driver',
            'reference'   => 'payout-ref-001',
            'customer'    => [
                'email'      => 'kwame@example.com',
                'first_name' => 'Kwame',
                'last_name'  => 'Mensah',
            ],
            'instrument'  => ['type' => 'phone', 'phone_number' => '+15550000000', 'country' => 'US'],
        ]);

        $this->assertInstanceOf(Payout::class, $payout);
        $this->assertSame('po_1', $payout->id);
        $this->assertSame('payout-ref-001', $payout->reference);
        $this->assertSame('Payout to driver', $payout->description);
        $this->assertSame('POST', $api->lastRequest()->getMethod());
        $this->assertSame('/v1/payouts', $api->lastRequest()->getUri()->getPath());
    }

    public function test_payout_hydrates_customer(): void
    {
        $api = new FakeApi([FakeApi::json(['data' => self::payoutFixture()])]);

        $payout = $api->client->payouts()->retrieve('po_1');

        $this->assertInstanceOf(Customer::class, $payout->customer);
        $this->assertSame('cus_1', $payout->customer->id);
        $this->assertSame('Kwame', $payout->customer->first_name);
        $this->assertSame('Mensah', $payout->customer->last_name);
        $this->assertSame('kwame@example.com', $payout->customer->email);
    }

    public function test_payout_hydrates_full_phone_instrument(): void
    {
        $api = new FakeApi([FakeApi::json(['data' => self::payoutFixture()])]);

        $payout = $api->client->payouts()->retrieve('po_1');

        $this->assertInstanceOf(PayoutInstrument::class, $payout->instrument);
        $this->assertSame('ins_1', $payout->instrument->id);
        $this->assertSame('phone', $payout->instrument->type);
        $this->assertSame('+15550000000', $payout->instrument->phone_number);
        $this->assertInstanceOf(Country::class, $payout->instrument->country);
        $this->assertSame('US', $payout->instrument->country->code);
    }

    public function test_payout_hydrates_gateway_as_array(): void
    {
        $api = new FakeApi([FakeApi::json(['data' => self::payoutFixture()])]);

        $payout = $api->client->payouts()->retrieve('po_1');

        $this->assertIsArray($payout->gateway);
        $this->assertSame('gw_txn_1', $payout->gateway['transaction']['id']);
        $this->assertSame('REF001', $payout->gateway['transaction']['reference']);
        $this->assertSame('ext_abc123', $payout->gateway['transaction']['external_id']);
    }

    public function test_payout_hydrates_failure(): void
    {
        $fixture = self::payoutFixture();
        $fixture['status'] = 'failed';
        $fixture['failure'] = [
            'code'    => [
                'value'    => 'INSTRUMENT_INVALID',
                'category' => 'INSTRUMENT_ERROR',
                'message'  => 'The phone number is not registered for mobile money.',
            ],
            'message' => 'The phone number is not registered for mobile money.',
        ];

        $api = new FakeApi([FakeApi::json(['data' => $fixture])]);

        $payout = $api->client->payouts()->retrieve('po_1');

        $this->assertInstanceOf(Failure::class, $payout->failure);
        $this->assertInstanceOf(FailureCode::class, $payout->failure->code);
        $this->assertSame('INSTRUMENT_INVALID', $payout->failure->code->value);
        $this->assertSame('INSTRUMENT_ERROR', $payout->failure->code->category);
        $this->assertSame(
            'The phone number is not registered for mobile money.',
            $payout->failure->message,
        );
    }

    public function test_payout_create_uses_the_top_level_path(): void
    {
        $api = new FakeApi([FakeApi::json(['data' => self::payoutFixture()], 201)]);

        $api->client->payouts()->create([
            'amount'     => 5000,
            'currency'   => 'USD',
            'method'     => 'test',
            'instrument' => ['type' => 'phone', 'phone_number' => '+15550000000', 'country' => 'US'],
        ]);

        $this->assertSame('POST', $api->lastRequest()->getMethod());
        $this->assertSame('/v1/payouts', $api->lastRequest()->getUri()->getPath());
    }

    public function test_payout_retrieve_uses_the_id_path(): void
    {
        $api = new FakeApi([FakeApi::json(['data' => ['id' => 'po_1']])]);

        $payout = $api->client->payouts()->retrieve('po_1');

        $this->assertSame('po_1', $payout->id);
        $this->assertSame('/v1/payouts/po_1', $api->lastRequest()->getUri()->getPath());
    }

    public function test_refund_via_payout_hydrates_the_payout_block_with_full_instrument(): void
    {
        $api = new FakeApi([FakeApi::json([
            'data' => [
                'id'               => 're_1',
                'amount'           => ['value' => 5000, 'currency' => 'USD'],
                'type'             => 'full',
                'execution_method' => 'payout',
                'status'           => 'processing',
                'payout'           => [
                    'id'           => 'po_9',
                    'gateway_code' => 'test',
                    'method'       => 'test',
                    'recipient'    => [
                        'id'           => 'ins_9',
                        'type'         => 'phone',
                        'phone_number' => '+15550000001',
                        'country'      => ['code' => 'DE', 'name' => 'Benin', 'flag' => null],
                    ],
                    'status'       => 'pending',
                ],
            ],
        ], 201)]);

        $refund = $api->client->refunds()->create('TRX1', ['amount' => 50, 'allow_payout' => true]);

        $this->assertInstanceOf(Refund::class, $refund);
        $this->assertSame('payout', $refund->execution_method);
        $this->assertInstanceOf(RefundPayout::class, $refund->payout);
        $this->assertSame('po_9', $refund->payout->id);
        $this->assertSame('test', $refund->payout->gateway_code);
        $this->assertInstanceOf(PayoutInstrument::class, $refund->payout->recipient);
        $this->assertSame('ins_9', $refund->payout->recipient->id);
        $this->assertSame('phone', $refund->payout->recipient->type);
        $this->assertSame('+15550000001', $refund->payout->recipient->phone_number);
        $this->assertSame('/v1/payment/intents/TRX1/refunds', $api->lastRequest()->getUri()->getPath());
    }

    /** @return array<string, mixed> */
    private static function payoutFixture(): array
    {
        return [
            'id'          => 'po_1',
            'amount'      => ['value' => 5000, 'currency' => 'USD'],
            'method'      => 'test',
            'status'      => 'pending',
            'reference'   => 'payout-ref-001',
            'description' => 'Payout to driver',
            'customer'    => [
                'id'         => 'cus_1',
                'first_name' => 'Kwame',
                'last_name'  => 'Mensah',
                'email'      => 'kwame@example.com',
                'avatar_url' => null,
            ],
            'instrument'  => [
                'id'           => 'ins_1',
                'type'         => 'phone',
                'phone_number' => '+15550000000',
                'country'      => ['code' => 'US', 'name' => "Cote d'Ivoire", 'flag' => null],
            ],
            'gateway'     => [
                'transaction' => [
                    'id'          => 'gw_txn_1',
                    'reference'   => 'REF001',
                    'external_id' => 'ext_abc123',
                ],
            ],
            'fee_amount'   => 50,
            'failure'      => null,
            'metadata'     => [],
            'initiated_at' => '2026-06-13T10:00:00Z',
            'completed_at' => null,
            'failed_at'    => null,
            'created_at'   => '2026-06-13T09:59:00Z',
        ];
    }
}
