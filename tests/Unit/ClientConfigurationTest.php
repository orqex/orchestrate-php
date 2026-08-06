<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Tests\Unit;

use GuzzleHttp\Client as GuzzleClient;
use Orqex\Orchestrate\ClientConfiguration;
use Orqex\Orchestrate\Exception\InvalidArgumentException;
use Orqex\Orchestrate\Http\GuzzleHttpClient;
use PHPUnit\Framework\TestCase;

final class ClientConfigurationTest extends TestCase
{
    public function test_it_accepts_a_bare_api_key_string(): void
    {
        $config = new ClientConfiguration('sk_live_123');

        $this->assertSame('sk_live_123', $config->apiKey);
        $this->assertSame('https://api.orqex.com/v1', $config->baseUri);
        $this->assertSame(2, $config->maxRetries);
        $this->assertInstanceOf(GuzzleHttpClient::class, $config->httpClient);
    }

    public function test_it_trims_trailing_slash_from_base_uri(): void
    {
        $config = new ClientConfiguration(['api_key' => 'sk_x', 'base_uri' => 'https://example.test/v1/']);

        $this->assertSame('https://example.test/v1', $config->baseUri);
    }

    public function test_it_throws_when_api_key_is_missing(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ClientConfiguration(['base_uri' => 'https://example.test']);
    }

    public function test_it_throws_when_api_key_has_wrong_prefix(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ClientConfiguration('pk_live_123');
    }

    public function test_it_wraps_a_guzzle_client(): void
    {
        $config = new ClientConfiguration(['api_key' => 'sk_x', 'http_client' => new GuzzleClient()]);

        $this->assertInstanceOf(GuzzleHttpClient::class, $config->httpClient);
    }

    public function test_it_configures_custom_dns_resolution_and_a_ca_bundle(): void
    {
        if (! defined('CURLOPT_RESOLVE')) {
            $this->markTestSkipped('The PHP cURL extension is not available.');
        }

        $caBundle = tempnam(sys_get_temp_dir(), 'orqex-ca-');
        $this->assertNotFalse($caBundle);

        try {
            $config = new ClientConfiguration([
                'api_key'    => 'sk_x',
                'base_uri'   => 'https://api.example.test/v1',
                'resolve_ip' => '192.0.2.10',
                'ca_bundle'  => $caBundle,
            ]);

            $client = $this->defaultGuzzleClient($config->httpClient);
            $curlOptions = $client->getConfig('curl');
            $resolveOption = constant('CURLOPT_RESOLVE');

            $this->assertSame($caBundle, $client->getConfig('verify'));
            $this->assertIsArray($curlOptions);
            $this->assertIsInt($resolveOption);
            $this->assertSame(
                ['api.example.test:443:192.0.2.10'],
                $curlOptions[$resolveOption],
            );
        } finally {
            unlink($caBundle);
        }
    }

    public function test_it_rejects_an_invalid_resolve_ip(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('valid IP address');

        new ClientConfiguration(['api_key' => 'sk_x', 'resolve_ip' => 'private-host']);
    }

    public function test_it_rejects_an_unreadable_ca_bundle(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('readable file');

        new ClientConfiguration(['api_key' => 'sk_x', 'ca_bundle' => '/missing/private-ca.pem']);
    }

    public function test_it_rejects_network_options_with_a_custom_client(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('cannot be combined');

        new ClientConfiguration([
            'api_key'     => 'sk_x',
            'resolve_ip'  => '192.0.2.10',
            'http_client' => new GuzzleClient(),
        ]);
    }

    public function test_it_rejects_an_unsupported_http_client(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ClientConfiguration(['api_key' => 'sk_x', 'http_client' => new \stdClass()]);
    }

    private function defaultGuzzleClient(GuzzleHttpClient $transport): GuzzleClient
    {
        $property = new \ReflectionProperty($transport, 'client');
        $client = $property->getValue($transport);

        $this->assertInstanceOf(GuzzleClient::class, $client);

        return $client;
    }
}
