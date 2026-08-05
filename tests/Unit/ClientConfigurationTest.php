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

    public function test_it_rejects_an_unsupported_http_client(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ClientConfiguration(['api_key' => 'sk_x', 'http_client' => new \stdClass()]);
    }
}
