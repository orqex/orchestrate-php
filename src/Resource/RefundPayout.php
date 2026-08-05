<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Resource;

use Orqex\Orchestrate\Enum\PayoutStatus;

/**
 * Summary of the payout used to execute a refund via payout. Present only
 * when the refund's `execution_method` is `payout`.
 *
 * @property string $id
 * @property Amount $amount Requested payout amount.
 * @property null|Amount $settlement_amount Amount processed by the payout gateway.
 * @property null|ExchangeRateDetails $exchange_rate Effective payout conversion.
 * @property null|string $gateway_code
 * @property null|string $method
 * @property null|PayoutInstrument $recipient Full destination instrument.
 * @property null|string $gateway_transaction_id
 * @property null|Amount $fee_amount Fee reported by the payout gateway.
 * @property null|string $status See {@see PayoutStatus}.
 * @property null|string $initiated_at
 * @property null|string $completed_at
 */
final class RefundPayout extends BaseResource
{
    protected static function casts(): array
    {
        return [
            'amount'            => Amount::class,
            'settlement_amount' => Amount::class,
            'exchange_rate'     => ExchangeRateDetails::class,
            'recipient'         => PayoutInstrument::class,
            'fee_amount'        => Amount::class,
        ];
    }
}
