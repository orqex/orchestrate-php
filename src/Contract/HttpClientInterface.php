<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Contract;

use Orqex\Orchestrate\Exception\ApiConnectionException;

/**
 * Minimal transport abstraction used by the SDK.
 *
 * The default implementation wraps Guzzle, but any client implementing this
 * interface can be injected through the `http_client` configuration option.
 * Implementations MUST NOT throw on non-2xx responses; they return the raw
 * status, headers and body, and the SDK maps them to typed exceptions.
 */
interface HttpClientInterface
{
    /**
     * Perform an HTTP request and return the raw response tuple.
     *
     * @param string $method HTTP verb (GET, POST, ...).
     * @param string $uri absolute request URI
     * @param array<string, string> $headers request headers
     * @param null|string $body raw request body (JSON), or null
     * @param float $timeout total request timeout in seconds
     * @param float $connectTimeout connection timeout in seconds
     *
     * @throws ApiConnectionException when the request cannot reach the API
     *
     * @return array{0: int, 1: array<string, array<int, string>>, 2: string}
     *                                                                        A tuple of [status code, response headers, response body]
     */
    public function request(
        string $method,
        string $uri,
        array $headers,
        ?string $body,
        float $timeout,
        float $connectTimeout,
    ): array;
}
