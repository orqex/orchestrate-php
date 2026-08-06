<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Tests\Laravel;

use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase;
use Orqex\Orchestrate\Exception\InvalidArgumentException;
use Orqex\Orchestrate\Laravel\Facades\Orchestrate;
use Orqex\Orchestrate\Laravel\OrchestrateServiceProvider;
use Orqex\Orchestrate\OrchestrateClient;
use Orqex\Orchestrate\Service\PaymentIntentService;

final class ServiceProviderTest extends TestCase
{
    public function test_the_client_is_bound_as_a_singleton(): void
    {
        $client = $this->app->make(OrchestrateClient::class);

        $this->assertInstanceOf(OrchestrateClient::class, $client);
        $this->assertSame('sk_test_laravel', $client->config->apiKey);
        $this->assertSame($client, $this->app->make('orchestrate'));
    }

    public function test_the_facade_exposes_services(): void
    {
        $this->assertInstanceOf(PaymentIntentService::class, Orchestrate::paymentIntents());
    }

    public function test_the_config_is_publishable(): void
    {
        $this->assertArrayHasKey('orchestrate', $this->app['config']->all());
        $this->assertSame('https://api.orqex.com/v1', $this->app['config']->get('orchestrate.base_uri'));
        $this->assertNull($this->app['config']->get('orchestrate.network.resolve_ip'));
        $this->assertNull($this->app['config']->get('orchestrate.network.ca_bundle'));
    }

    public function test_network_configuration_is_forwarded_to_the_client(): void
    {
        $this->app['config']->set('orchestrate.network.resolve_ip', 'invalid-address');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('valid IP address');

        $this->app->make(OrchestrateClient::class);
    }

    /**
     * @param Application $app
     *
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [OrchestrateServiceProvider::class];
    }

    /**
     * @param Application $app
     *
     * @return array<string, class-string>
     */
    protected function getPackageAliases($app): array
    {
        return ['Orchestrate' => Orchestrate::class];
    }

    /**
     * @param Application $app
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('orchestrate.secret_key', 'sk_test_laravel');
    }
}
