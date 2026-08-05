<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Service;

use Orqex\Orchestrate\Http\RequestOptions;
use Orqex\Orchestrate\Resource\Requery;

/**
 * Re-checking a payment against the provider that holds it.
 */
final class RequeryService extends AbstractService
{
    /**
     * Ask Orchestrate to re-check a payment with its provider and reconcile
     * the stored status.
     *
     * A fallback for a payment that has not settled, not a polling strategy:
     * webhooks remain the primary signal.
     *
     * @param null|array<string,mixed>|RequestOptions $opts
     */
    public function requery(string $paymentIntentId, null|array|RequestOptions $opts = null): Requery
    {
        return $this->requestResource(
            Requery::class,
            'POST',
            $this->buildPath('/payment/intents/%s/requery', $paymentIntentId),
            [],
            $opts,
        );
    }
}
