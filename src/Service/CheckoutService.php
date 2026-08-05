<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Service;

use Orqex\Orchestrate\Http\ApiResponse;
use Orqex\Orchestrate\Http\RequestOptions;
use Orqex\Orchestrate\Resource\Checkout;

/**
 * Hosted checkout sessions.
 *
 * @see https://docs.orqex.com
 */
final class CheckoutService extends AbstractService
{
    /**
     * Create a hosted checkout session and its underlying payment intent.
     *
     * @param array<string, mixed> $params
     * @param null|array<string,mixed>|RequestOptions $opts
     */
    public function create(array $params, null|array|RequestOptions $opts = null): Checkout
    {
        return $this->hydrate($this->requestRaw('POST', '/payment/checkouts', $params, $opts));
    }

    /**
     * Retrieve a checkout session by id.
     *
     * @param null|array<string,mixed>|RequestOptions $opts
     */
    public function retrieve(string $checkoutId, null|array|RequestOptions $opts = null): Checkout
    {
        return $this->hydrate(
            $this->requestRaw('GET', $this->buildPath('/payment/checkouts/%s', $checkoutId), [], $opts),
        );
    }

    /**
     * Flatten the `{checkout, payment}` envelope into a single Checkout resource
     * whose `payment` attribute holds the bound payment intent.
     */
    private function hydrate(ApiResponse $response): Checkout
    {
        $data = is_array($response->json['data'] ?? null) ? $response->json['data'] : [];

        /** @var array<string, mixed> $checkout */
        $checkout = is_array($data['checkout'] ?? null) ? $data['checkout'] : $data;

        if (array_key_exists('payment', $data)) {
            $checkout['payment'] = $data['payment'];
        }

        return Checkout::constructFrom($checkout, $response);
    }
}
