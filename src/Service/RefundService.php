<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Service;

use Orqex\Orchestrate\Enum\RefundReason;
use Orqex\Orchestrate\Http\RequestOptions;
use Orqex\Orchestrate\Resource\Collection;
use Orqex\Orchestrate\Resource\Refund;
use Orqex\Orchestrate\Resource\RefundAvailability;

/**
 * Refunds nested under a payment intent.
 */
final class RefundService extends AbstractService
{
    /**
     * Create a full or partial refund of a completed payment.
     *
     * Required params: `amount` (major units, at most the
     * `refundable_amount` reported by {@see self::availability()}) and
     * `reason` (one of the {@see RefundReason} values). Optional: `note`
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
     * What the payment can still be refunded for.
     *
     * Answers before an amount is submitted: the remaining balance, whether
     * a refund is possible at all, how it would be executed, and which
     * refund types the balance allows.
     *
     * @param null|array<string,mixed>|RequestOptions $opts
     */
    public function availability(
        string $paymentIntentId,
        null|array|RequestOptions $opts = null,
    ): RefundAvailability {
        return $this->requestResource(
            RefundAvailability::class,
            'GET',
            $this->buildPath('/payment/intents/%s/refunds/availability', $paymentIntentId),
            [],
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
