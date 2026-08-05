<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Tests\Service;

use Orqex\Orchestrate\Enum\PaymentMethodStatus;
use Orqex\Orchestrate\Resource\Collection;
use Orqex\Orchestrate\Resource\GatewayInspection;
use Orqex\Orchestrate\Resource\MethodStatus;
use Orqex\Orchestrate\Resource\Payout;
use Orqex\Orchestrate\Resource\RefundsSummary;
use Orqex\Orchestrate\Resource\Requery;
use Orqex\Orchestrate\Tests\Support\FakeApi;
use PHPUnit\Framework\TestCase;

/**
 * The endpoints added or renamed when the SDK caught up with the public API:
 * requery replacing reconcile, payout listing, sync and inspection, attempt
 * inspection, payment method status, and the refunds summary carried on a
 * payment intent.
 */
final class PublicApiSurfaceTest extends TestCase
{
    public function test_requery_posts_to_the_requery_path_and_hydrates(): void
    {
        $api = new FakeApi([FakeApi::json(['data' => [
            'id'                 => 'rq_1',
            'status'             => 'completed',
            'payment_intent_id'  => 'pi_1',
            'payment_attempt_id' => 'att_1',
            'before'             => ['intent_status' => 'pending', 'attempt_status' => 'processing'],
            'after'              => ['intent_status' => 'completed', 'attempt_status' => 'completed'],
            'attempts'           => ['count' => 1, 'requeried' => []],
        ]])]);

        $requery = $api->client->requeries()->requery('pi_1');

        $this->assertInstanceOf(Requery::class, $requery);
        $this->assertSame('rq_1', $requery->id);
        $this->assertSame('completed', $requery->after['intent_status']);
        $this->assertSame('POST', $api->lastRequest()->getMethod());
        $this->assertSame('/v1/payment/intents/pi_1/requery', $api->lastRequest()->getUri()->getPath());
    }

    public function test_payouts_can_be_listed_with_filters(): void
    {
        $api = new FakeApi([FakeApi::json([
            'data'       => [['id' => 'po_1'], ['id' => 'po_2']],
            'pagination' => ['has_more_pages' => false, 'next_cursor' => null],
        ])]);

        $page = $api->client->payouts()->all(['status' => 'completed', 'per_page' => 2]);

        $this->assertInstanceOf(Collection::class, $page);
        $this->assertCount(2, $page);
        $this->assertInstanceOf(Payout::class, $page->data[0]);
        $this->assertSame('/v1/payouts', $api->lastRequest()->getUri()->getPath());
        $this->assertStringContainsString('status=completed', $api->lastRequest()->getUri()->getQuery());
    }

    public function test_payout_sync_hits_the_sync_path(): void
    {
        $api = new FakeApi([FakeApi::json(['data' => ['id' => 'po_1', 'status' => 'completed']])]);

        $payout = $api->client->payouts()->sync('po_1');

        $this->assertSame('completed', $payout->status);
        $this->assertSame('GET', $api->lastRequest()->getMethod());
        $this->assertSame('/v1/payouts/po_1/sync', $api->lastRequest()->getUri()->getPath());
    }

    public function test_payout_inspect_returns_the_raw_provider_record(): void
    {
        $api = new FakeApi([FakeApi::json(['data' => [
            'gateway'                => 'test',
            'gateway_transaction_id' => 'gw_1',
            'retrieved_at'           => '2026-08-02T12:00:00+00:00',
            'payload'                => ['simulated' => 'completed'],
        ]])]);

        $inspection = $api->client->payouts()->inspect('po_1');

        $this->assertInstanceOf(GatewayInspection::class, $inspection);
        $this->assertSame('gw_1', $inspection->gateway_transaction_id);
        $this->assertSame(['simulated' => 'completed'], $inspection->payload);
        $this->assertSame('/v1/payouts/po_1/inspect', $api->lastRequest()->getUri()->getPath());
    }

    public function test_attempt_inspect_returns_the_raw_provider_record(): void
    {
        $api = new FakeApi([FakeApi::json(['data' => [
            'gateway'                => 'test',
            'gateway_transaction_id' => 'gw_2',
            'retrieved_at'           => '2026-08-02T12:00:00+00:00',
            'payload'                => [],
        ]])]);

        $inspection = $api->client->attempts()->inspect('pi_1', 'att_1');

        $this->assertInstanceOf(GatewayInspection::class, $inspection);
        $this->assertSame('gw_2', $inspection->gateway_transaction_id);
        $this->assertSame('/v1/payment/intents/pi_1/attempts/att_1/inspect', $api->lastRequest()->getUri()->getPath());
    }

    public function test_payment_method_status_is_retrieved_and_typed(): void
    {
        $api = new FakeApi([FakeApi::json([
            'data' => ['method' => 'test', 'country' => 'US', 'status' => 'operational'],
            'meta' => ['environment' => 'sandbox', 'window_minutes' => 60, 'ttl_seconds' => 300],
        ])]);

        $status = $api->client->status()->paymentMethod('test', 'US');

        $this->assertInstanceOf(MethodStatus::class, $status);
        $this->assertSame('operational', $status->status);
        $this->assertTrue(PaymentMethodStatus::from($status->status)->isUsable());
        $this->assertSame('/v1/utils/status/payment/method/test/US', $api->lastRequest()->getUri()->getPath());
        $this->assertSame(300, $status->lastResponse()?->json['meta']['ttl_seconds']);
    }

    public function test_a_payment_intent_carries_its_refunds_summary(): void
    {
        $api = new FakeApi([FakeApi::json(['data' => [
            'id'              => 'pi_1',
            'status'          => 'partially_refunded',
            'refunds_summary' => [
                'refunded_amount'    => ['value' => 19.5, 'currency' => 'EUR'],
                'pending_amount'     => ['value' => 0.0, 'currency' => 'EUR'],
                'refundable_amount'  => ['value' => 30.5, 'currency' => 'EUR'],
                'has_pending_refund' => false,
            ],
        ]])]);

        $intent = $api->client->paymentIntents()->retrieve('pi_1');

        $this->assertInstanceOf(RefundsSummary::class, $intent->refunds_summary);
        $this->assertSame(30.5, $intent->refunds_summary->refundable_amount->value);
        $this->assertSame(19.5, $intent->refunds_summary->refunded_amount->value);
        $this->assertSame('EUR', $intent->refunds_summary->refundable_amount->currency);
        $this->assertFalse($intent->refunds_summary->has_pending_refund);
    }
}
