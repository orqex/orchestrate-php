<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Service;

use Orqex\Orchestrate\Http\RequestOptions;
use Orqex\Orchestrate\Resource\Collection;
use Orqex\Orchestrate\Resource\GatewayInspection;
use Orqex\Orchestrate\Resource\PaymentAttempt;

/**
 * Payment attempts nested under a payment intent.
 */
final class PaymentAttemptService extends AbstractService
{
    /**
     * Start a new payment attempt against an existing intent.
     *
     * @param array<string, mixed> $params
     * @param null|array<string,mixed>|RequestOptions $opts
     */
    public function create(
        string $paymentIntentId,
        array $params,
        null|array|RequestOptions $opts = null,
    ): PaymentAttempt {
        return $this->requestResource(
            PaymentAttempt::class,
            'POST',
            $this->buildPath('/payment/intents/%s/attempts', $paymentIntentId),
            $params,
            $opts,
        );
    }

    /**
     * Confirm an attempt that requires customer action (e.g. submit an OTP).
     * The `$attemptId` must be the current active attempt on the intent.
     *
     * @param array<string, mixed> $params
     * @param null|array<string,mixed>|RequestOptions $opts
     */
    public function confirm(
        string $paymentIntentId,
        string $attemptId,
        array $params = [],
        null|array|RequestOptions $opts = null,
    ): PaymentAttempt {
        return $this->requestResource(
            PaymentAttempt::class,
            'POST',
            $this->buildPath('/payment/intents/%s/attempts/%s/confirm', $paymentIntentId, $attemptId),
            $params,
            $opts,
        );
    }

    /**
     * List the attempts of a payment intent.
     *
     * @param array<string, mixed> $params
     * @param null|array<string,mixed>|RequestOptions $opts
     *
     * @return Collection<PaymentAttempt>
     */
    public function all(
        string $paymentIntentId,
        array $params = [],
        null|array|RequestOptions $opts = null,
    ): Collection {
        return $this->requestCollection(
            PaymentAttempt::class,
            $this->buildPath('/payment/intents/%s/attempts', $paymentIntentId),
            $params,
            $opts,
        );
    }

    /**
     * Retrieve a single attempt of a payment intent.
     *
     * @param null|array<string,mixed>|RequestOptions $opts
     */
    public function retrieve(
        string $paymentIntentId,
        string $attemptId,
        null|array|RequestOptions $opts = null,
    ): PaymentAttempt {
        return $this->requestResource(
            PaymentAttempt::class,
            'GET',
            $this->buildPath('/payment/intents/%s/attempts/%s', $paymentIntentId, $attemptId),
            [],
            $opts,
        );
    }

    /**
     * Fetch the record the underlying provider holds for this attempt, for
     * support and debugging. Use it when Orchestrate and the provider seem to
     * disagree.
     *
     * @param null|array<string,mixed>|RequestOptions $opts
     */
    public function inspect(
        string $paymentIntentId,
        string $attemptId,
        null|array|RequestOptions $opts = null,
    ): GatewayInspection {
        return $this->requestResource(
            GatewayInspection::class,
            'GET',
            $this->buildPath('/payment/intents/%s/attempts/%s/inspect', $paymentIntentId, $attemptId),
            [],
            $opts,
        );
    }
}
