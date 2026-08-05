<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Http;

/**
 * Per-request options layered on top of the client configuration.
 *
 * Accepts a loose array so callers can pass a plain array as the last
 * argument of any service method:
 *
 *   $client->paymentIntents()->create($params, ['idempotency_key' => $key]);
 */
final class RequestOptions
{
    /**
     * @param array<string, string> $headers
     */
    public function __construct(
        public ?string $idempotencyKey = null,
        public array $headers = [],
        public ?float $timeout = null,
    ) {}

    /**
     * @param null|array<string, mixed>|self $value
     */
    public static function parse(null|array|self $value): self
    {
        if ($value instanceof self) {
            return $value;
        }

        if ($value === null) {
            return new self();
        }

        /** @var array<string, string> $headers */
        $headers = isset($value['headers']) && is_array($value['headers']) ? $value['headers'] : [];

        return new self(
            idempotencyKey: isset($value['idempotency_key']) ? (string) $value['idempotency_key'] : null,
            headers: $headers,
            timeout: isset($value['timeout']) ? (float) $value['timeout'] : null,
        );
    }
}
