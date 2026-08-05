<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Service;

use Orqex\Orchestrate\Http\RequestOptions;
use Orqex\Orchestrate\Resource\MethodStatus;

/**
 * Operational health signals you can use to steer your own interface.
 */
final class StatusService extends AbstractService
{
    /**
     * Retrieve the operational status of a payment method in a country.
     *
     * Use it to hide or de-prioritise a method while it is struggling. The
     * observation window and a cache TTL are returned in the response `meta`
     * block, reachable through {@see MethodStatus::lastResponse()}.
     *
     * @param string $methodCode A code returned by the methods discovery endpoint
     * @param null|array<string,mixed>|RequestOptions $opts
     */
    public function paymentMethod(
        string $methodCode,
        string $countryCode,
        null|array|RequestOptions $opts = null,
    ): MethodStatus {
        return $this->requestResource(
            MethodStatus::class,
            'GET',
            $this->buildPath('/utils/status/payment/method/%s/%s', $methodCode, $countryCode),
            [],
            $opts,
        );
    }
}
