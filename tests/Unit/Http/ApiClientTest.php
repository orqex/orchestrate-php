<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Tests\Unit\Http;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Orqex\Orchestrate\Exception\ApiConnectionException;
use Orqex\Orchestrate\Exception\AuthenticationException;
use Orqex\Orchestrate\Exception\InvalidRequestException;
use Orqex\Orchestrate\Exception\NotFoundException;
use Orqex\Orchestrate\Exception\NotImplementedException;
use Orqex\Orchestrate\Exception\RateLimitException;
use Orqex\Orchestrate\Exception\ServerException;
use Orqex\Orchestrate\Exception\UnexpectedValueException;
use Orqex\Orchestrate\Tests\Support\FakeApi;
use PHPUnit\Framework\TestCase;

final class ApiClientTest extends TestCase
{
    public function test_it_sends_auth_and_idempotency_headers_on_post(): void
    {
        $api = new FakeApi([FakeApi::json(['data' => ['id' => 'TRX1']], 201)]);

        $api->client->request('POST', '/payment/intents', ['amount' => 100]);

        $request = $api->lastRequest();
        $this->assertSame('Bearer sk_test_abc123', $request->getHeaderLine('Authorization'));
        $this->assertSame('application/json', $request->getHeaderLine('Accept'));
        $this->assertStringStartsWith('orq_', $request->getHeaderLine('X-Idempotency-Key'));
        $this->assertStringContainsString('Orqex-Orchestrate-PHP/', $request->getHeaderLine('User-Agent'));
        $this->assertSame('{"amount":100}', (string) $request->getBody());
    }

    public function test_it_honours_an_explicit_idempotency_key(): void
    {
        $api = new FakeApi([FakeApi::json(['data' => []], 201)]);

        $api->client->request('POST', '/payment/intents', [], ['idempotency_key' => 'my-key-123456']);

        $this->assertSame('my-key-123456', $api->lastRequest()->getHeaderLine('X-Idempotency-Key'));
    }

    public function test_it_appends_query_params_on_get(): void
    {
        $api = new FakeApi([FakeApi::json(['data' => [], 'pagination' => []])]);

        $api->client->request('GET', '/payment/intents', ['status' => 'completed', 'per_page' => 5]);

        $this->assertSame('status=completed&per_page=5', $api->lastRequest()->getUri()->getQuery());
    }

    public function test_it_maps_status_codes_to_typed_exceptions(): void
    {
        $cases = [
            [401, AuthenticationException::class],
            [404, NotFoundException::class],
            [422, InvalidRequestException::class],
            [429, RateLimitException::class],
            [501, NotImplementedException::class],
            [500, ServerException::class],
        ];

        foreach ($cases as [$status, $exception]) {
            $api = new FakeApi([FakeApi::json(['message' => 'nope'], $status)]);

            try {
                $api->client->request('GET', '/payment/intents/x');
                $this->fail("Expected {$exception} for status {$status}");
            } catch (\Throwable $e) {
                $this->assertInstanceOf($exception, $e, "status {$status}");
            }
        }
    }

    public function test_invalid_request_exposes_validation_errors(): void
    {
        $api = new FakeApi([FakeApi::json([
            'message' => 'The given data was invalid.',
            'errors'  => ['amount' => ['The amount must be at least 1.']],
        ], 422)]);

        try {
            $api->client->request('POST', '/payment/intents', []);
            $this->fail('Expected InvalidRequestException');
        } catch (InvalidRequestException $e) {
            $this->assertSame(422, $e->httpStatus);
            $this->assertSame('The amount must be at least 1.', $e->firstError());
        }
    }

    public function test_it_retries_transient_failures_then_succeeds(): void
    {
        $api = new FakeApi(
            [
                FakeApi::json(['message' => 'busy'], 503),
                FakeApi::json(['data' => ['id' => 'TRX1']], 201),
            ],
            ['max_retries' => 2],
        );

        $response = $api->client->request('POST', '/payment/intents', ['amount' => 100]);

        $this->assertSame(201, $response->statusCode);
        $this->assertCount(2, $api->requests());
    }

    public function test_it_reuses_the_idempotency_key_across_retries(): void
    {
        $api = new FakeApi(
            [
                FakeApi::json(['message' => 'busy'], 503),
                FakeApi::json(['data' => []], 201),
            ],
            ['max_retries' => 2],
        );

        $api->client->request('POST', '/payment/intents', ['amount' => 100]);

        $keys = array_map(static fn ($r): string => $r->getHeaderLine('X-Idempotency-Key'), $api->requests());
        $this->assertCount(2, $keys);
        $this->assertSame($keys[0], $keys[1]);
    }

    public function test_it_does_not_retry_when_disabled(): void
    {
        $api = new FakeApi([FakeApi::json(['message' => 'busy'], 503)]);

        $this->expectException(ServerException::class);

        try {
            $api->client->request('GET', '/payment/intents');
        } finally {
            $this->assertCount(1, $api->requests());
        }
    }

    public function test_it_wraps_connection_errors(): void
    {
        $api = new FakeApi([new ConnectException('timeout', new Request('GET', 'x'))]);

        $this->expectException(ApiConnectionException::class);

        $api->client->request('GET', '/payment/intents');
    }

    public function test_it_throws_on_malformed_json(): void
    {
        $api = new FakeApi([new Response(200, ['Content-Type' => 'application/json'], 'not-json')]);

        $this->expectException(UnexpectedValueException::class);

        $api->client->request('GET', '/payment/intents');
    }
}
