<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Resource;

use Orqex\Orchestrate\Enum\RefundExecutionMethod;
use Orqex\Orchestrate\Enum\RefundType;

/**
 * What a payment can still be refunded for.
 *
 * `refundable_amount` is authoritative: it already reserves in-flight refunds,
 * so a refund up to that amount is the one guaranteed not to be rejected on
 * balance.
 *
 * `available_types` omits `partial` when no amount smaller than the balance
 * can be expressed in the currency — a balance of one whole unit in a currency
 * without a minor unit leaves nothing between zero and the whole.
 *
 * @property bool $is_refundable
 * @property Amount $refundable_amount
 * @property Amount $refunded_amount
 * @property Amount $pending_amount
 * @property bool $has_pending_refund
 * @property list<string> $available_types See {@see RefundType}.
 * @property null|string $execution_method See {@see RefundExecutionMethod}.
 * @property null|string $unavailable_reason Human-readable; do not branch on the text.
 */
final class RefundAvailability extends BaseResource
{
    public function allows(RefundType $type): bool
    {
        return in_array($type->value, $this->available_types ?? [], true);
    }

    protected static function casts(): array
    {
        return [
            'refundable_amount' => Amount::class,
            'refunded_amount'   => Amount::class,
            'pending_amount'    => Amount::class,
        ];
    }
}
