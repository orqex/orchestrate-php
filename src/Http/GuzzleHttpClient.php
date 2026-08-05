<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Http;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\RequestOptions as GuzzleRequestOptions;
use Orqex\Orchestrate\Contract\HttpClientInterface;
use Orqex\Orchestrate\Exception\ApiConnectionException;

/**
 * Default transport, backed by Guzzle.
 *
 * A pre-configured Guzzle client may be supplied (for connection pooling,
 * proxies, custom middleware or test doubles); otherwise one is created
 * lazily with sensible defaults.
 */
final class GuzzleHttpClient implements HttpClientInterface
{
    private GuzzleClientInterface $client;

    public function __construct(?GuzzleClientInterface $client = null)
    {
        $this->client = $client ?? new GuzzleClient();
    }

    public function request(
        string $method,
        string $uri,
        array $headers,
        ?string $body,
        float $timeout,
        float $connectTimeout,
    ): array {
        try {
            $response = $this->client->request($method, $uri, [
                GuzzleRequestOptions::HEADERS         => $headers,
                GuzzleRequestOptions::BODY            => $body,
                GuzzleRequestOptions::HTTP_ERRORS     => false,
                GuzzleRequestOptions::TIMEOUT         => $timeout,
                GuzzleRequestOptions::CONNECT_TIMEOUT => $connectTimeout,
            ]);
        } catch (ConnectException $e) {
            throw new ApiConnectionException(
                'Could not connect to the Orqex API: ' . $e->getMessage(),
                previous: $e,
            );
        } catch (GuzzleException|TransferException $e) {
            throw new ApiConnectionException(
                'HTTP transport error while calling the Orqex API: ' . $e->getMessage(),
                previous: $e,
            );
        }

        /** @var array<string, array<int, string>> $responseHeaders */
        $responseHeaders = $response->getHeaders();

        return [
            $response->getStatusCode(),
            $responseHeaders,
            (string) $response->getBody(),
        ];
    }
}
