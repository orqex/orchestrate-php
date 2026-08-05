<?php

declare(strict_types=1);

namespace Orqex\Orchestrate;

/**
 * Library metadata and a convenience factory for the {@see OrchestrateClient}.
 */
final class Orchestrate
{
    public const VERSION = '0.2.0'; // x-release-please-version

    public const DEFAULT_BASE_URI = 'https://api.orqex.com/v1';

    /**
     * Build a client from an API key string or a configuration array.
     *
     * @param array<string, mixed>|string $config An `sk_...` secret key, or a configuration array.
     */
    public static function client(array|string $config): OrchestrateClient
    {
        return new OrchestrateClient($config);
    }
}
