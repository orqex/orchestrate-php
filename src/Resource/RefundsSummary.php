<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Resource;

/**
 * Refund position of a payment.
 *
 * `refundable_amount` is authoritative: it already reserves in-flight refunds,
 * so a refund up to that amount is the one guaranteed not to be rejected on
 * balance.
 *
 * @property Amount $refunded_amount
 * @property Amount $pending_amount
 * @property Amount $refundable_amount
 * @property bool $has_pending_refund
 */
final class RefundsSummary extends BaseResource
{
    protected static function casts(): array
    {
        return [
            'refunded_amount'   => Amount::class,
            'pending_amount'    => Amount::class,
            'refundable_amount' => Amount::class,
        ];
    }
}
