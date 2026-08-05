<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Tests\Service;

use Orqex\Orchestrate\Resource\Failover;
use Orqex\Orchestrate\Resource\Failure;
use Orqex\Orchestrate\Resource\FailureCode;
use Orqex\Orchestrate\Resource\NextAction;
use Orqex\Orchestrate\Resource\PaymentAttempt;
use Orqex\Orchestrate\Resource\PaymentMethod;
use Orqex\Orchestrate\Tests\Support\FakeApi;
use PHPUnit\Framework\TestCase;

final class PaymentAttemptServiceTest extends TestCase
{
    public function test_create_posts_to_attempts_and_returns_attempt(): void
    {
        $api = new FakeApi([FakeApi::json([
            'data' => ['id' => 'pa_1', 'status' => 'processing'],
        ], 201)]);

        $attempt = $api->client->attempts()->create('TRX1', [
            'method_code' => 'test',
            'country'     => 'US',
            'phone'       => ['number' => '0700000000', 'country' => 'US'],
        ]);

        $this->assertInstanceOf(PaymentAttempt::class, $attempt);
        $this->assertSame('pa_1', $attempt->id);
        $this->assertSame('POST', $api->lastRequest()->getMethod());
        $this->assertSame('/v1/payment/intents/TRX1/attempts', $api->lastRequest()->getUri()->getPath());
    }

    public function test_confirm_posts_to_the_correct_path_with_attempt_id(): void
    {
        $api = new FakeApi([FakeApi::json([
            'data' => ['id' => 'pa_1', 'status' => 'succeeded'],
        ])]);

        $attempt = $api->client->attempts()->confirm('TRX1', 'pa_1', ['otp' => '123456']);

        $this->assertInstanceOf(PaymentAttempt::class, $attempt);
        $this->assertSame('POST', $api->lastRequest()->getMethod());
        $this->assertSame('/v1/payment/intents/TRX1/attempts/pa_1/confirm', $api->lastRequest()->getUri()->getPath());
    }

    public function test_all_returns_collection_of_attempts(): void
    {
        $api = new FakeApi([FakeApi::json([
            'data'       => [['id' => 'pa_1'], ['id' => 'pa_2']],
            'pagination' => ['total' => 2],
        ])]);

        $collection = $api->client->attempts()->all('TRX1');

        $this->assertCount(2, $collection);
        $this->assertSame('GET', $api->lastRequest()->getMethod());
        $this->assertSame('/v1/payment/intents/TRX1/attempts', $api->lastRequest()->getUri()->getPath());
    }

    public function test_retrieve_uses_the_correct_path(): void
    {
        $api = new FakeApi([FakeApi::json(['data' => ['id' => 'pa_1']])]);

        $api->client->attempts()->retrieve('TRX1', 'pa_1');

        $this->assertSame('GET', $api->lastRequest()->getMethod());
        $this->assertSame('/v1/payment/intents/TRX1/attempts/pa_1', $api->lastRequest()->getUri()->getPath());
    }

    public function test_failure_code_is_hydrated_as_object(): void
    {
        $api = new FakeApi([FakeApi::json([
            'data' => [
                'id'      => 'pa_1',
                'status'  => 'failed',
                'failure' => [
                    'code'    => [
                        'value'    => 'ROUTING_NOT_CONFIGURED',
                        'category' => 'MERCHANT_CONFIG_ERROR',
                        'message'  => 'No routing rule configured for this method.',
                    ],
                    'message' => 'No routing rule configured for this method.',
                ],
            ],
        ])]);

        $attempt = $api->client->attempts()->retrieve('TRX1', 'pa_1');

        $this->assertInstanceOf(Failure::class, $attempt->failure);
        $this->assertInstanceOf(FailureCode::class, $attempt->failure->code);
        $this->assertSame('ROUTING_NOT_CONFIGURED', $attempt->failure->code->value);
        $this->assertSame('MERCHANT_CONFIG_ERROR', $attempt->failure->code->category);
    }

    public function test_next_action_exposes_dial_code(): void
    {
        $api = new FakeApi([FakeApi::json([
            'data' => [
                'id'          => 'pa_1',
                'status'      => 'requires_action',
                'next_action' => [
                    'type'      => 'approve_on_phone',
                    'dial_code' => '#144*1#',
                ],
            ],
        ])]);

        $attempt = $api->client->attempts()->retrieve('TRX1', 'pa_1');

        $this->assertInstanceOf(NextAction::class, $attempt->next_action);
        $this->assertSame('approve_on_phone', $attempt->next_action->type);
        $this->assertSame('#144*1#', $attempt->next_action->dial_code);
    }

    public function test_failover_and_method_requires_phone_are_hydrated(): void
    {
        $api = new FakeApi([FakeApi::json([
            'data' => [
                'id'       => 'pa_1',
                'status'   => 'failed',
                'method'   => [
                    'value'          => 'test',
                    'label'          => 'Test method',
                    'category'       => 'mobile_money',
                    'requires_phone' => true,
                ],
                'failover' => [
                    'is_failover' => true,
                    'decision'    => 'eligible',
                    'hint'        => 'The payment was retried on a backup gateway.',
                ],
            ],
        ])]);

        $attempt = $api->client->attempts()->retrieve('TRX1', 'pa_1');

        $this->assertInstanceOf(PaymentMethod::class, $attempt->method);
        $this->assertTrue($attempt->method->requires_phone);

        $this->assertInstanceOf(Failover::class, $attempt->failover);
        $this->assertTrue($attempt->failover->is_failover);
        $this->assertSame('eligible', $attempt->failover->decision);
    }
}
