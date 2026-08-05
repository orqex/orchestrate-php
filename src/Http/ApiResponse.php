<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Http;

/**
 * Immutable view over a successful API response.
 *
 * Resources keep a reference to the response they were hydrated from so
 * callers can read the HTTP status, headers and request id after the fact.
 */
final class ApiResponse
{
    /**
     * @param array<string, array<int, string>> $headers
     * @param array<string, mixed> $json
     */
    public function __construct(
        public readonly int $statusCode,
        public readonly array $headers,
        public readonly array $json,
        public readonly string $body,
    ) {}

    public function requestId(): ?string
    {
        return $this->header('X-Request-Id');
    }

    public function isIdempotentReplay(): bool
    {
        return $this->header('X-Idempotent-Replayed') === 'true';
    }

    public function header(string $name): ?string
    {
        foreach ($this->headers as $key => $values) {
            if (strcasecmp($key, $name) === 0) {
                return $values[0] ?? null;
            }
        }

        return null;
    }
}
