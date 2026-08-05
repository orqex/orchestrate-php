<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Resource;

use Orqex\Orchestrate\Enum\PayoutStatus;

/**
 * An outbound payout to a typed instrument.
 *
 * @property string $id
 * @property Amount $amount Major units, the same scale as a payment.
 * @property null|Amount $settlement_amount Amount processed by the payout gateway.
 * @property null|ExchangeRateDetails $exchange_rate Effective payout conversion.
 * @property null|string $method Payout method code, as configured for your project.
 * @property string $status See {@see PayoutStatus}.
 * @property null|string $reference Merchant-supplied idempotency reference.
 * @property null|string $description Human-readable description of the payout.
 * @property null|Customer $customer Customer the payout is attributed to.
 * @property null|PayoutInstrument $instrument
 * @property array<string,mixed> $gateway Gateway transaction details (id, reference, external_id).
 * @property null|Amount $fee_amount Fee reported by the payout gateway.
 * @property null|Failure $failure Structured failure details when the payout has failed.
 * @property array<string,mixed> $metadata
 * @property null|string $initiated_at
 * @property null|string $completed_at
 * @property null|string $failed_at
 * @property null|string $created_at
 */
final class Payout extends BaseResource
{
    protected static function casts(): array
    {
        return [
            'amount'            => Amount::class,
            'settlement_amount' => Amount::class,
            'exchange_rate'     => ExchangeRateDetails::class,
            'customer'          => Customer::class,
            'instrument'        => PayoutInstrument::class,
            'fee_amount'        => Amount::class,
            'failure'           => Failure::class,
        ];
    }
}
