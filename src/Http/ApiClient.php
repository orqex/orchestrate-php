<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Http;

use Orqex\Orchestrate\ClientConfiguration;
use Orqex\Orchestrate\Contract\HttpClientInterface;
use Orqex\Orchestrate\Exception\ApiConnectionException;
use Orqex\Orchestrate\Exception\ApiException;
use Orqex\Orchestrate\Exception\UnexpectedValueException;
use Orqex\Orchestrate\Orchestrate;
use Orqex\Orchestrate\OrchestrateClient;

/**
 * API-level HTTP client: builds, sends and interprets requests against Orqex.
 *
 * Sits above the raw transport ({@see HttpClientInterface})
 * and below the public {@see OrchestrateClient}. Handles
 * authentication, idempotency keys, automatic retries with exponential backoff
 * on transient failures, JSON decoding and mapping error responses to typed
 * exceptions. Service classes depend on this client, not on the transport.
 */
final class ApiClient
{
    private const SAFE_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    private const RETRY_BASE_MS = 200;

    private const RETRY_MAX_MS = 2000;

    private const MAX_RETRY_AFTER_MS = 60000;

    /**
     * Test seam: when set, replaces the real `usleep` between retries.
     *
     * @var null|(\Closure(int): void)
     */
    public ?\Closure $sleeper = null;

    public function __construct(private readonly ClientConfiguration $config) {}

    /**
     * @param array<string, mixed> $params body (unsafe methods) or query string (safe methods)
     * @param null|array<string,mixed>|RequestOptions $options
     */
    public function request(
        string $method,
        string $path,
        array $params = [],
        null|array|RequestOptions $options = null,
    ): ApiResponse {
        $method = strtoupper($method);
        $options = RequestOptions::parse($options);
        $isSafe = in_array($method, self::SAFE_METHODS, true);

        $uri = $this->config->baseUri . '/' . ltrim($path, '/');
        $body = null;

        if ($isSafe) {
            if ($params !== []) {
                $uri .= '?' . http_build_query($params);
            }
        } else {
            $body = $params === [] ? '{}' : (string) json_encode($params, JSON_THROW_ON_ERROR);
        }

        $headers = $this->buildHeaders($isSafe, $options);
        $timeout = $options->timeout ?? $this->config->timeout;

        return $this->send($method, $uri, $headers, $body, $timeout);
    }

    /**
     * @param array<string, string> $headers
     */
    private function send(string $method, string $uri, array $headers, ?string $body, float $timeout): ApiResponse
    {
        $attempt = 0;

        while (true) {
            try {
                [$status, $responseHeaders, $rawBody] = $this->config->httpClient->request(
                    $method,
                    $uri,
                    $headers,
                    $body,
                    $timeout,
                    $this->config->connectTimeout,
                );
            } catch (ApiConnectionException $e) {
                if ($this->shouldRetry($attempt, null)) {
                    $this->backoff(++$attempt, null);

                    continue;
                }

                throw $e;
            }

            if ($status >= 400 && $this->shouldRetry($attempt, $status)) {
                $this->backoff(++$attempt, $this->retryAfter($responseHeaders));

                continue;
            }

            return $this->interpret($status, $responseHeaders, $rawBody);
        }
    }

    /**
     * @param array<string, array<int, string>> $headers
     */
    private function interpret(int $status, array $headers, string $rawBody): ApiResponse
    {
        $decoded = $this->decode($rawBody);
        $response = new ApiResponse($status, $headers, $decoded, $rawBody);

        if ($status < 400) {
            return $response;
        }

        $message = is_string($decoded['message'] ?? null)
            ? $decoded['message']
            : sprintf('The API returned an unexpected %d response.', $status);

        /** @var array<string, array<int, string>> $errors */
        $errors = is_array($decoded['errors'] ?? null) ? $decoded['errors'] : [];

        throw ApiException::fromHttpStatus(
            httpStatus: $status,
            message: $message,
            errors: $errors,
            requestId: $response->requestId(),
            rawBody: $decoded,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function decode(string $rawBody): array
    {
        if (trim($rawBody) === '') {
            return [];
        }

        try {
            /** @var array<string, mixed> $decoded */
            $decoded = json_decode($rawBody, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new UnexpectedValueException('Failed to decode the API response as JSON: ' . $e->getMessage(), 0, $e);
        }

        return is_array($decoded) ? $decoded : ['data' => $decoded];
    }

    /**
     * @return array<string, string>
     */
    private function buildHeaders(bool $isSafe, RequestOptions $options): array
    {
        $headers = [
            'Authorization' => 'Bearer ' . $this->config->apiKey,
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
            'User-Agent'    => 'Orqex-Orchestrate-PHP/' . Orchestrate::VERSION . ' php/' . PHP_VERSION,
        ];

        if (! $isSafe) {
            $headers['X-Idempotency-Key'] = $options->idempotencyKey ?? $this->generateIdempotencyKey();
        }

        return array_merge($headers, $this->config->headers, $options->headers);
    }

    private function generateIdempotencyKey(): string
    {
        return 'orq_' . bin2hex(random_bytes(16));
    }

    private function shouldRetry(int $attempt, ?int $status): bool
    {
        if ($attempt >= $this->config->maxRetries) {
            return false;
        }

        if ($status === null) {
            return true;
        }

        return $status === 429 || ($status >= 500 && $status !== 501);
    }

    private function backoff(int $attempt, ?int $retryAfterSeconds): void
    {
        // Exponential backoff with full jitter, capped, to avoid thundering herds.
        $exponential = (int) min(self::RETRY_BASE_MS * (2 ** ($attempt - 1)), self::RETRY_MAX_MS);
        $delayMs = max(self::RETRY_BASE_MS, (int) ($exponential * (random_int(50, 100) / 100)));

        if ($retryAfterSeconds !== null) {
            $delayMs = max($delayMs, min($retryAfterSeconds * 1000, self::MAX_RETRY_AFTER_MS));
        }

        if ($this->sleeper !== null) {
            ($this->sleeper)($delayMs);

            return;
        }

        usleep($delayMs * 1000);
    }

    /**
     * @param array<string, array<int, string>> $headers
     */
    private function retryAfter(array $headers): ?int
    {
        foreach ($headers as $name => $values) {
            if (strcasecmp($name, 'Retry-After') === 0 && isset($values[0]) && is_numeric($values[0])) {
                return (int) $values[0];
            }
        }

        return null;
    }
}
