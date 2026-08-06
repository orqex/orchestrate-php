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
use Orqex\Orchestrate\Exception\InvalidArgumentException;

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

    /** @var array<int, string> */
    private array $sensitiveTransportValues;

    /**
     * @param array<int, string> $sensitiveTransportValues
     */
    public function __construct(
        ?GuzzleClientInterface $client = null,
        #[\SensitiveParameter]
        array $sensitiveTransportValues = [],
    ) {
        $this->client = $client ?? new GuzzleClient();
        $this->sensitiveTransportValues = array_values(array_filter(
            $sensitiveTransportValues,
            static fn (string $value): bool => $value !== '',
        ));
    }

    /**
     * Keep private routing details out of dumps and exception pages.
     *
     * @return array<string, mixed>
     */
    public function __debugInfo(): array
    {
        return [
            'client'                   => $this->client::class,
            'customNetworkConfigured'  => $this->sensitiveTransportValues !== [],
        ];
    }

    public static function fromNetworkConfiguration(
        string $baseUri,
        #[\SensitiveParameter]
        ?string $resolveIp,
        #[\SensitiveParameter]
        ?string $caBundle,
    ): self {
        $options = [
            GuzzleRequestOptions::VERIFY => self::validatedCaBundle($caBundle),
        ];
        $sensitiveValues = array_values(array_filter([$resolveIp, $caBundle]));

        if ($resolveIp !== null) {
            $options['curl'] = [
                self::curlResolveOption() => [self::resolveEntry($baseUri, $resolveIp)],
            ];
        }

        return new self(
            client: new GuzzleClient($options),
            sensitiveTransportValues: $sensitiveValues,
        );
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
                'Could not connect to the Orqex API: ' . $this->sanitise($e->getMessage()),
                previous: $this->safePrevious($e),
            );
        } catch (GuzzleException|TransferException $e) {
            throw new ApiConnectionException(
                'HTTP transport error while calling the Orqex API: ' . $this->sanitise($e->getMessage()),
                previous: $this->safePrevious($e),
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

    private static function validatedCaBundle(?string $caBundle): bool|string
    {
        if ($caBundle === null) {
            return true;
        }

        if (! is_file($caBundle) || ! is_readable($caBundle)) {
            throw new InvalidArgumentException('The configured CA bundle must be a readable file.');
        }

        return $caBundle;
    }

    private static function curlResolveOption(): int
    {
        if (! defined('CURLOPT_RESOLVE')) {
            throw new InvalidArgumentException('Custom DNS resolution requires the PHP cURL extension.');
        }

        $option = constant('CURLOPT_RESOLVE');

        if (! is_int($option)) {
            throw new InvalidArgumentException('Custom DNS resolution is unavailable in this PHP runtime.');
        }

        return $option;
    }

    private static function resolveEntry(string $baseUri, string $resolveIp): string
    {
        if (filter_var($resolveIp, FILTER_VALIDATE_IP) === false) {
            throw new InvalidArgumentException('The "resolve_ip" option must be a valid IP address.');
        }

        $scheme = parse_url($baseUri, PHP_URL_SCHEME);
        $host = parse_url($baseUri, PHP_URL_HOST);
        $configuredPort = parse_url($baseUri, PHP_URL_PORT);

        if (! is_string($scheme) || ! in_array(strtolower($scheme), ['http', 'https'], true) || ! is_string($host) || $host === '') {
            throw new InvalidArgumentException('Custom DNS resolution requires a valid HTTP or HTTPS base URI.');
        }

        $port = is_int($configuredPort) ? $configuredPort : (strtolower($scheme) === 'https' ? 443 : 80);
        $address = str_contains($resolveIp, ':') ? "[{$resolveIp}]" : $resolveIp;

        return "{$host}:{$port}:{$address}";
    }

    private function sanitise(string $message): string
    {
        foreach ($this->sensitiveTransportValues as $value) {
            $message = str_replace([$value, rawurlencode($value)], '[redacted]', $message);
        }

        return $message;
    }

    private function safePrevious(\Throwable $exception): ?\Throwable
    {
        return $this->sensitiveTransportValues === [] ? $exception : null;
    }
}
