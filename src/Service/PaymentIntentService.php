<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Service;

use Orqex\Orchestrate\Http\RequestOptions;
use Orqex\Orchestrate\Resource\Collection;
use Orqex\Orchestrate\Resource\Country;
use Orqex\Orchestrate\Resource\CountryList;
use Orqex\Orchestrate\Resource\PaymentIntent;
use Orqex\Orchestrate\Resource\PaymentMethod;

/**
 * Payment intents: the merchant-facing payment request and its lifecycle.
 *
 * Attempts are managed via {@see PaymentAttemptService}
 * (accessible through `$client->attempts()`).
 */
final class PaymentIntentService extends AbstractService
{
    /**
     * Create a payment intent. Pass an optional `attempt` to start a payment
     * in the same call.
     *
     * @param array<string, mixed> $params
     * @param null|array<string,mixed>|RequestOptions $opts
     */
    public function create(array $params, null|array|RequestOptions $opts = null): PaymentIntent
    {
        return $this->requestResource(PaymentIntent::class, 'POST', '/payment/intents', $params, $opts);
    }

    /**
     * List payment intents. Supports filters (`status`, `channel`,
     * `customer_id`, `created_at[gte]`, `created_at[lte]`) and `per_page`.
     *
     * @param array<string, mixed> $params
     * @param null|array<string,mixed>|RequestOptions $opts
     *
     * @return Collection<PaymentIntent>
     */
    public function all(array $params = [], null|array|RequestOptions $opts = null): Collection
    {
        return $this->requestCollection(PaymentIntent::class, '/payment/intents', $params, $opts);
    }

    /**
     * Retrieve a payment intent by id.
     *
     * @param null|array<string,mixed>|RequestOptions $opts
     */
    public function retrieve(string $paymentIntentId, null|array|RequestOptions $opts = null): PaymentIntent
    {
        return $this->requestResource(
            PaymentIntent::class,
            'GET',
            $this->buildPath('/payment/intents/%s', $paymentIntentId),
            [],
            $opts,
        );
    }

    /**
     * List the countries available for payment on a given intent.
     *
     * Returns a {@see CountryList} containing the hydrated countries and
     * the `supports_any_country` / `total` metadata from the API envelope.
     *
     * @param null|array<string,mixed>|RequestOptions $opts
     */
    public function availableCountries(string $paymentIntentId, null|array|RequestOptions $opts = null): CountryList
    {
        $response = $this->requestRaw(
            'GET',
            $this->buildPath('/payment/intents/%s/countries', $paymentIntentId),
            [],
            $opts,
        );

        $rows = $response->json['data'] ?? [];
        $meta = $response->json['meta'] ?? [];

        $countries = [];

        if (is_array($rows)) {
            foreach ($rows as $row) {
                if (is_array($row)) {
                    $countries[] = Country::constructFrom($row, $response);
                }
            }
        }

        return CountryList::constructFrom([
            'data'                 => $countries,
            'supports_any_country' => (bool) ($meta['supports_any_country'] ?? false),
            'total'                => (int) ($meta['total'] ?? count($countries)),
        ], $response);
    }

    /**
     * List the payment methods available for a given intent and country.
     *
     * Pass an optional `currency` (ISO 4217) query parameter to filter
     * methods by DCC currency.
     *
     * @param array<string, mixed> $params e.g. `['currency' => 'EUR']`
     * @param null|array<string,mixed>|RequestOptions $opts
     *
     * @return Collection<PaymentMethod>
     */
    public function availableMethods(
        string $paymentIntentId,
        string $countryCode,
        array $params = [],
        null|array|RequestOptions $opts = null,
    ): Collection {
        return $this->requestCollection(
            PaymentMethod::class,
            $this->buildPath('/payment/intents/%s/countries/%s/methods', $paymentIntentId, $countryCode),
            $params,
            $opts,
        );
    }

    /**
     * Authorise an active payment attempt that requires customer action.
     *
     * Typically called after `next_action.type` is `collect_otp` — pass the
     * OTP as `otp`. Other confirmation payloads go in `confirmation_data`.
     *
     * @param array<string, mixed> $params e.g. `['otp' => '123456']` or `['confirmation_data' => [...]]`
     * @param null|array<string,mixed>|RequestOptions $opts
     */
    public function authorize(
        string $paymentIntentId,
        array $params = [],
        null|array|RequestOptions $opts = null,
    ): PaymentIntent {
        return $this->requestResource(
            PaymentIntent::class,
            'POST',
            $this->buildPath('/payment/intents/%s/authorize', $paymentIntentId),
            $params,
            $opts,
        );
    }
}
