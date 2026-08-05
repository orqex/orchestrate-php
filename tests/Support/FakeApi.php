<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Tests\Support;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Orqex\Orchestrate\OrchestrateClient;
use Psr\Http\Message\RequestInterface;

/**
 * Builds an OrchestrateClient backed by a queue of canned HTTP responses,
 * recording the outgoing requests for assertions.
 */
final class FakeApi
{
    /** @var array<int, array<string, mixed>> */
    public array $history = [];

    public readonly OrchestrateClient $client;

    private MockHandler $mock;

    /**
     * @param array<int, Response|\Throwable> $responses
     * @param array<string, mixed> $config
     */
    public function __construct(array $responses, array $config = [])
    {
        $this->mock = new MockHandler($responses);
        $stack = HandlerStack::create($this->mock);
        $stack->push(Middleware::history($this->history));

        $this->client = new OrchestrateClient(array_merge([
            'api_key'     => 'sk_test_abc123',
            'max_retries' => 0,
            'http_client' => new GuzzleClient(['handler' => $stack]),
        ], $config));

        // Never sleep during tests.
        $this->client->apiClient()->sleeper = static fn (int $ms): null => null;
    }

    /**
     * Convenience builder for a JSON response.
     *
     * @param array<string, mixed> $body
     * @param array<string, string> $headers
     */
    public static function json(array $body, int $status = 200, array $headers = []): Response
    {
        return new Response(
            $status,
            array_merge(['Content-Type' => 'application/json'], $headers),
            (string) json_encode($body),
        );
    }

    public function lastRequest(): RequestInterface
    {
        // @var RequestInterface $request
        return $this->history[array_key_last($this->history)]['request'];
    }

    /**
     * @return array<int, RequestInterface>
     */
    public function requests(): array
    {
        return array_map(
            static fn (array $entry): RequestInterface => $entry['request'],
            $this->history,
        );
    }
}
