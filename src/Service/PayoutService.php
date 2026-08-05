<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Service;

use Orqex\Orchestrate\Http\RequestOptions;
use Orqex\Orchestrate\Resource\Collection;
use Orqex\Orchestrate\Resource\GatewayInspection;
use Orqex\Orchestrate\Resource\Payout;

/**
 * Outbound payouts to a typed instrument (phone, bank account or crypto
 * address).
 */
final class PayoutService extends AbstractService
{
    /**
     * Create an outbound payout.
     *
     * `amount` is in major units, the same scale as a payment intent.
     * `reference` must be unique within the project; a duplicate returns 409.
     *
     * @param array<string, mixed> $params
     * @param null|array<string,mixed>|RequestOptions $opts
     */
    public function create(
        array $params,
        null|array|RequestOptions $opts = null,
    ): Payout {
        return $this->requestResource(
            Payout::class,
            'POST',
            '/payouts',
            $params,
            $opts,
        );
    }

    /**
     * Retrieve a single payout.
     *
     * @param null|array<string,mixed>|RequestOptions $opts
     */
    public function retrieve(
        string $payoutId,
        null|array|RequestOptions $opts = null,
    ): Payout {
        return $this->requestResource(
            Payout::class,
            'GET',
            $this->buildPath('/payouts/%s', $payoutId),
            [],
            $opts,
        );
    }

    /**
     * List payouts, newest first.
     *
     * Supported filters: `status`, `method`, `currency`, `customer_id`,
     * `created_at[gte]`, `created_at[lte]`, `per_page`.
     *
     * @param array<string, mixed> $params
     * @param null|array<string,mixed>|RequestOptions $opts
     *
     * @return Collection<Payout>
     */
    public function all(
        array $params = [],
        null|array|RequestOptions $opts = null,
    ): Collection {
        return $this->requestCollection(Payout::class, '/payouts', $params, $opts);
    }

    /**
     * Re-check a payout that is not yet final with its provider and return the
     * refreshed payout. A payout that is already final comes back unchanged.
     *
     * @param null|array<string,mixed>|RequestOptions $opts
     */
    public function sync(
        string $payoutId,
        null|array|RequestOptions $opts = null,
    ): Payout {
        return $this->requestResource(
            Payout::class,
            'GET',
            $this->buildPath('/payouts/%s/sync', $payoutId),
            [],
            $opts,
        );
    }

    /**
     * Fetch the record the underlying provider holds for this payout, for
     * support and debugging.
     *
     * @param null|array<string,mixed>|RequestOptions $opts
     */
    public function inspect(
        string $payoutId,
        null|array|RequestOptions $opts = null,
    ): GatewayInspection {
        return $this->requestResource(
            GatewayInspection::class,
            'GET',
            $this->buildPath('/payouts/%s/inspect', $payoutId),
            [],
            $opts,
        );
    }
}
