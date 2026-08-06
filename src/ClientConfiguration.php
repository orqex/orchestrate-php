<?php

declare(strict_types=1);

namespace Orqex\Orchestrate;

use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use Orqex\Orchestrate\Contract\HttpClientInterface;
use Orqex\Orchestrate\Exception\InvalidArgumentException;
use Orqex\Orchestrate\Http\GuzzleHttpClient;

/**
 * Resolved, validated configuration for an {@see OrchestrateClient}.
 *
 * Build it from a bare secret key:
 *
 *   new ClientConfiguration('sk_live_...');
 *
 * or from an array for full control:
 *
 *   new ClientConfiguration([
 *       'api_key'         => 'sk_live_...',
 *       'base_uri'        => 'https://api.orqex.com/v1',
 *       'timeout'         => 30.0,
 *       'connect_timeout' => 10.0,
 *       'max_retries'     => 2,
 *       'http_client'     => $myGuzzleClientOrHttpClientInterface,
 *       'headers'         => ['X-Custom' => 'value'],
 *   ]);
 *
 * Custom DNS resolution and private CA trust can be configured when using
 * the default transport:
 *
 *   new ClientConfiguration([
 *       'api_key'    => 'sk_live_...',
 *       'resolve_ip' => '192.0.2.10',
 *       'ca_bundle'  => '/path/to/private-ca.pem',
 *   ]);
 */
final class ClientConfiguration
{
    public readonly string $apiKey;

    public readonly string $baseUri;

    public readonly float $timeout;

    public readonly float $connectTimeout;

    public readonly int $maxRetries;

    public readonly HttpClientInterface $httpClient;

    /** @var array<string, string> */
    public readonly array $headers;

    /**
     * @param array<string, mixed>|string $config
     */
    public function __construct(#[\SensitiveParameter] array|string $config)
    {
        $config = is_string($config) ? ['api_key' => $config] : $config;

        $apiKey = isset($config['api_key']) ? trim((string) $config['api_key']) : '';

        if ($apiKey === '') {
            throw new InvalidArgumentException('An Orqex API key is required. Pass an `sk_...` secret key.');
        }

        if (! str_starts_with($apiKey, 'sk_')) {
            throw new InvalidArgumentException('Invalid API key: a secret key must start with "sk_".');
        }

        $this->apiKey = $apiKey;
        $this->baseUri = rtrim((string) ($config['base_uri'] ?? Orchestrate::DEFAULT_BASE_URI), '/');
        $this->timeout = (float) ($config['timeout'] ?? 30.0);
        $this->connectTimeout = (float) ($config['connect_timeout'] ?? 10.0);
        $this->maxRetries = max(0, (int) ($config['max_retries'] ?? 2));

        /** @var array<string, string> $headers */
        $headers = isset($config['headers']) && is_array($config['headers']) ? $config['headers'] : [];
        $this->headers = $headers;

        $this->httpClient = $this->resolveHttpClient(
            client: $config['http_client'] ?? null,
            resolveIp: $this->optionalString($config, 'resolve_ip'),
            caBundle: $this->optionalString($config, 'ca_bundle'),
        );
    }

    /**
     * Keep the secret key out of var_dump, dd() and Laravel's exception pages.
     *
     * @return array<string, mixed>
     */
    public function __debugInfo(): array
    {
        return [
            'apiKey'         => $this->maskedApiKey(),
            'baseUri'        => $this->baseUri,
            'timeout'        => $this->timeout,
            'connectTimeout' => $this->connectTimeout,
            'maxRetries'     => $this->maxRetries,
            'httpClient'     => $this->httpClient::class,
            'headers'        => $this->headers,
        ];
    }

    /**
     * Never serialise the secret key: a cached or queued payload must not carry it.
     *
     * @return array<string, mixed>
     */
    public function __serialize(): array
    {
        throw new InvalidArgumentException(
            'An Orqex client configuration must not be serialised: it holds a secret key. '
            . 'Rebuild it from configuration instead.',
        );
    }

    /**
     * The environment and last four characters, enough to tell two keys apart
     * without revealing either.
     */
    public function maskedApiKey(): string
    {
        $parts = explode('_', $this->apiKey);
        $prefix = count($parts) >= 2 ? $parts[0] . '_' . $parts[1] : 'sk';

        return $prefix . '_***' . substr($this->apiKey, -4);
    }

    private function resolveHttpClient(
        mixed $client,
        #[\SensitiveParameter]
        ?string $resolveIp,
        #[\SensitiveParameter]
        ?string $caBundle,
    ): HttpClientInterface {
        if ($client !== null && ($resolveIp !== null || $caBundle !== null)) {
            throw new InvalidArgumentException(
                'The "resolve_ip" and "ca_bundle" options cannot be combined with "http_client". '
                . 'Configure network routing on the custom client instead.',
            );
        }

        if ($client instanceof HttpClientInterface) {
            return $client;
        }

        if ($client instanceof GuzzleClientInterface) {
            return new GuzzleHttpClient($client);
        }

        if ($client === null) {
            return GuzzleHttpClient::fromNetworkConfiguration(
                baseUri: $this->baseUri,
                resolveIp: $resolveIp,
                caBundle: $caBundle,
            );
        }

        throw new InvalidArgumentException(
            'The "http_client" option must implement ' . HttpClientInterface::class
            . ' or ' . GuzzleClientInterface::class . '.',
        );
    }

    /**
     * @param array<string, mixed> $config
     */
    private function optionalString(array $config, string $key): ?string
    {
        $value = $config[$key] ?? null;

        if ($value === null || $value === '') {
            return null;
        }

        if (! is_string($value)) {
            throw new InvalidArgumentException("The \"{$key}\" option must be a string.");
        }

        $value = trim($value);

        return $value !== '' ? $value : null;
    }
}
