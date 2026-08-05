<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Resource;

use Orqex\Orchestrate\Enum\PayoutStatus;

/**
 * Summary of the payout used to execute a refund via payout. Present only
 * when the refund's `execution_method` is `payout`.
 *
 * @property string $id
 * @property null|string $gateway_code
 * @property null|string $method
 * @property null|PayoutInstrument $recipient Full destination instrument.
 * @property null|string $gateway_transaction_id
 * @property null|int $fee_amount Payout fee in minor units.
 * @property null|string $status See {@see PayoutStatus}.
 * @property null|string $initiated_at
 * @property null|string $completed_at
 */
final class RefundPayout extends BaseResource
{
    protected static function casts(): array
    {
        return [
            'recipient' => PayoutInstrument::class,
        ];
    }
}
