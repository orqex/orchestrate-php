<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Resource;

use Orqex\Orchestrate\Enum\RefundExecutionMethod;
use Orqex\Orchestrate\Enum\RefundReason;
use Orqex\Orchestrate\Enum\RefundStatus;
use Orqex\Orchestrate\Enum\RefundType;

/**
 * A full or partial refund of a completed payment.
 *
 * @property string $id
 * @property Amount $amount
 * @property null|Amount $processed_amount Amount sent to the payment gateway.
 * @property null|ExchangeRateDetails $exchange_rate Effective conversion applied to the refund.
 * @property null|string $type See {@see RefundType}.
 * @property null|string $execution_method See {@see RefundExecutionMethod}.
 * @property null|string $status See {@see RefundStatus}.
 * @property null|array{value: string, label: string} $reason Structured reason; `value` maps to {@see RefundReason}.
 * @property null|string $note Free-text note attached to the refund.
 * @property null|string $failure_code
 * @property null|string $failure_message
 * @property array<string,mixed> $metadata
 * @property null|RefundPayout $payout Present only when executed via payout.
 * @property null|string $completed_at
 * @property null|string $failed_at
 * @property null|string $created_at
 */
final class Refund extends BaseResource
{
    protected static function casts(): array
    {
        return [
            'amount'           => Amount::class,
            'processed_amount' => Amount::class,
            'exchange_rate'    => ExchangeRateDetails::class,
            'payout'           => RefundPayout::class,
        ];
    }
}
