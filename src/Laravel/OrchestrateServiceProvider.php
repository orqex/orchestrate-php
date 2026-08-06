<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Laravel;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Orqex\Orchestrate\OrchestrateClient;

/**
 * Registers the Orqex client in the Laravel container.
 *
 * Auto-discovered in Laravel applications. The package itself does not
 * depend on Laravel; this provider is only loaded when the framework is
 * present (`illuminate/support`).
 */
final class OrchestrateServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/orchestrate.php', 'orchestrate');

        $this->app->singleton(OrchestrateClient::class, static function (Application $app): OrchestrateClient {
            /** @var array<string, mixed> $config */
            $config = $app['config']['orchestrate'] ?? [];
            $network = isset($config['network']) && is_array($config['network']) ? $config['network'] : [];

            return new OrchestrateClient([
                'api_key'         => $config['secret_key'] ?? null,
                'base_uri'        => $config['base_uri'] ?? 'https://api.orqex.com/v1',
                'timeout'         => $config['timeout'] ?? 30.0,
                'connect_timeout' => $config['connect_timeout'] ?? 10.0,
                'max_retries'     => $config['max_retries'] ?? 2,
                'resolve_ip'      => $network['resolve_ip'] ?? null,
                'ca_bundle'       => $network['ca_bundle'] ?? null,
            ]);
        });

        $this->app->alias(OrchestrateClient::class, 'orchestrate');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/orchestrate.php' => $this->app->configPath('orchestrate.php'),
            ], 'orchestrate-config');
        }
    }

    /**
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [OrchestrateClient::class, 'orchestrate'];
    }
}
