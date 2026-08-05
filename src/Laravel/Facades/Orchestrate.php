<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use Orqex\Orchestrate\OrchestrateClient;

/**
 * @method static \Orqex\Orchestrate\Service\CheckoutService checkouts()
 * @method static \Orqex\Orchestrate\Service\PaymentIntentService paymentIntents()
 * @method static \Orqex\Orchestrate\Service\PaymentAttemptService attempts()
 * @method static \Orqex\Orchestrate\Service\RefundService refunds()
 * @method static \Orqex\Orchestrate\Service\PayoutService payouts()
 * @method static \Orqex\Orchestrate\Service\RequeryService requeries()
 * @method static \Orqex\Orchestrate\Service\StatusService status()
 * @method static \Orqex\Orchestrate\Service\ExchangeRateService exchangeRates()
 * @method static \Orqex\Orchestrate\Http\ApiResponse request(string $method, string $path, array $params = [], $opts = null)
 *
 * @see OrchestrateClient
 */
final class Orchestrate extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'orchestrate';
    }
}
