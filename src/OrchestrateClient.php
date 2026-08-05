<?php

declare(strict_types=1);

namespace Orqex\Orchestrate;

use Orqex\Orchestrate\Http\ApiClient;
use Orqex\Orchestrate\Http\ApiResponse;
use Orqex\Orchestrate\Http\RequestOptions;
use Orqex\Orchestrate\Service\AbstractService;
use Orqex\Orchestrate\Service\CheckoutService;
use Orqex\Orchestrate\Service\ExchangeRateService;
use Orqex\Orchestrate\Service\PaymentAttemptService;
use Orqex\Orchestrate\Service\PaymentIntentService;
use Orqex\Orchestrate\Service\PayoutService;
use Orqex\Orchestrate\Service\RefundService;
use Orqex\Orchestrate\Service\RequeryService;
use Orqex\Orchestrate\Service\StatusService;

/**
 * The Orqex API client.
 *
 *   $orqex = new OrchestrateClient('sk_live_...');
 *   $intent = $orqex->paymentIntents()->create([...]);
 *
 * Services are exposed as memoised accessor methods, so the same instance
 * is reused for the lifetime of the client.
 */
final class OrchestrateClient
{
    public readonly ClientConfiguration $config;

    private readonly ApiClient $apiClient;

    /**
     * @var array<class-string<AbstractService>, AbstractService>
     */
    private array $services = [];

    /**
     * @param array<string, mixed>|ClientConfiguration|string $config
     */
    public function __construct(array|ClientConfiguration|string $config)
    {
        $this->config = $config instanceof ClientConfiguration ? $config : new ClientConfiguration($config);
        $this->apiClient = new ApiClient($this->config);
    }

    public function checkouts(): CheckoutService
    {
        return $this->service(CheckoutService::class);
    }

    public function paymentIntents(): PaymentIntentService
    {
        return $this->service(PaymentIntentService::class);
    }

    public function attempts(): PaymentAttemptService
    {
        return $this->service(PaymentAttemptService::class);
    }

    public function refunds(): RefundService
    {
        return $this->service(RefundService::class);
    }

    public function payouts(): PayoutService
    {
        return $this->service(PayoutService::class);
    }

    public function requeries(): RequeryService
    {
        return $this->service(RequeryService::class);
    }

    public function status(): StatusService
    {
        return $this->service(StatusService::class);
    }

    public function exchangeRates(): ExchangeRateService
    {
        return $this->service(ExchangeRateService::class);
    }

    /**
     * Low-level escape hatch returning the raw, un-hydrated response.
     *
     * @param array<string, mixed> $params
     * @param null|array<string,mixed>|RequestOptions $opts
     */
    public function request(
        string $method,
        string $path,
        array $params = [],
        null|array|RequestOptions $opts = null,
    ): ApiResponse {
        return $this->apiClient->request($method, $path, $params, $opts);
    }

    /**
     * The underlying API client (advanced use and testing).
     */
    public function apiClient(): ApiClient
    {
        return $this->apiClient;
    }

    /**
     * @template T of AbstractService
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    private function service(string $class): AbstractService
    {
        // @var T
        return $this->services[$class] ??= new $class($this->apiClient);
    }
}
