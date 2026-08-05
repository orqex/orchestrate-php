<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Resource;

use Orqex\Orchestrate\Enum\PaymentAttemptStatus;

/**
 * A gateway-level execution of a payment intent.
 *
 * @property string $id
 * @property null|PaymentMethod $method
 * @property Amount $amount
 * @property null|float $exchange_rate
 * @property null|float $correction_rate
 * @property string $status See {@see PaymentAttemptStatus}.
 * @property array<string,mixed> $gateway Gateway transaction details.
 * @property null|NextAction $next_action
 * @property null|Failure $failure Structured failure details when the attempt has failed.
 * @property null|Failover $failover Failover evaluation for this attempt.
 * @property null|string $processing_at When the attempt last entered the processing state.
 * @property null|string $action_required_at When the attempt last started waiting on a customer action.
 * @property null|string $completed_at
 * @property null|string $failed_at
 * @property null|string $expired_at When the attempt expired after its waiting window ran out.
 * @property null|string $created_at
 */
final class PaymentAttempt extends BaseResource
{
    protected static function casts(): array
    {
        return [
            'method'      => PaymentMethod::class,
            'amount'      => Amount::class,
            'next_action' => NextAction::class,
            'failure'     => Failure::class,
            'failover'    => Failover::class,
        ];
    }
}
