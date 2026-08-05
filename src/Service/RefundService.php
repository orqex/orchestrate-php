<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Service;

use Orqex\Orchestrate\Enum\RefundReason;
use Orqex\Orchestrate\Http\RequestOptions;
use Orqex\Orchestrate\Resource\Collection;
use Orqex\Orchestrate\Resource\Refund;

/**
 * Refunds nested under a payment intent.
 */
final class RefundService extends AbstractService
{
    /**
     * Create a full or partial refund of a completed payment.
     *
     * Required params: `amount` (major units, at most the intent's
     * `refunds_summary.refundable_amount`) and `reason` (one of the
     * {@see RefundReason} values). Optional: `note`
     * (free text), `metadata`, `allow_payout`.
     *
     * @param array<string, mixed> $params
     * @param null|array<string,mixed>|RequestOptions $opts
     */
    public function create(
        string $paymentIntentId,
        array $params,
        null|array|RequestOptions $opts = null,
    ): Refund {
        return $this->requestResource(
            Refund::class,
            'POST',
            $this->buildPath('/payment/intents/%s/refunds', $paymentIntentId),
            $params,
            $opts,
        );
    }

    /**
     * List the refunds of a payment intent.
     *
     * @param array<string, mixed> $params
     * @param null|array<string,mixed>|RequestOptions $opts
     *
     * @return Collection<Refund>
     */
    public function all(
        string $paymentIntentId,
        array $params = [],
        null|array|RequestOptions $opts = null,
    ): Collection {
        return $this->requestCollection(
            Refund::class,
            $this->buildPath('/payment/intents/%s/refunds', $paymentIntentId),
            $params,
            $opts,
        );
    }

    /**
     * Retrieve a single refund of a payment intent.
     *
     * @param null|array<string,mixed>|RequestOptions $opts
     */
    public function retrieve(
        string $paymentIntentId,
        string $refundId,
        null|array|RequestOptions $opts = null,
    ): Refund {
        return $this->requestResource(
            Refund::class,
            'GET',
            $this->buildPath('/payment/intents/%s/refunds/%s', $paymentIntentId, $refundId),
            [],
            $opts,
        );
    }
}
