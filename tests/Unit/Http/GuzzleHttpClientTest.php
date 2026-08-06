<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Tests\Unit\Http;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Request;
use Orqex\Orchestrate\Exception\ApiConnectionException;
use Orqex\Orchestrate\Http\GuzzleHttpClient;
use PHPUnit\Framework\TestCase;

final class GuzzleHttpClientTest extends TestCase
{
    public function test_it_redacts_network_details_from_transport_errors(): void
    {
        $ipAddress = '192.0.2.10';
        $caBundle = '/private/network/origin-ca.pem';
        $handler = new MockHandler([
            new ConnectException(
                "Failed to connect to {$ipAddress} with {$caBundle}",
                new Request('GET', 'https://api.example.test/v1'),
            ),
        ]);
        $transport = new GuzzleHttpClient(
            client: new GuzzleClient(['handler' => $handler]),
            sensitiveTransportValues: [$ipAddress, $caBundle],
        );

        try {
            $transport->request(
                method: 'GET',
                uri: 'https://api.example.test/v1',
                headers: [],
                body: null,
                timeout: 1.0,
                connectTimeout: 1.0,
            );
            $this->fail('Expected an API connection exception.');
        } catch (ApiConnectionException $exception) {
            $this->assertStringNotContainsString($ipAddress, $exception->getMessage());
            $this->assertStringNotContainsString($caBundle, $exception->getMessage());
            $this->assertStringContainsString('[redacted]', $exception->getMessage());
            $this->assertNull($exception->getPrevious());
        }
    }

    public function test_dumping_the_transport_hides_network_details(): void
    {
        $transport = new GuzzleHttpClient(
            sensitiveTransportValues: ['192.0.2.10', '/private/network/origin-ca.pem'],
        );

        ob_start();
        var_dump($transport);
        $dump = (string) ob_get_clean();

        $this->assertStringNotContainsString('192.0.2.10', $dump);
        $this->assertStringNotContainsString('/private/network/origin-ca.pem', $dump);
        $this->assertStringContainsString('customNetworkConfigured', $dump);
    }
}
