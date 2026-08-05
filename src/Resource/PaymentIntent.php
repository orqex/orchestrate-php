<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Resource;

use Orqex\Orchestrate\Enum\PaymentIntentStatus;

/**
 * A merchant-created payment request.
 *
 * @property string $id Public payment id (e.g. "TRX...").
 * @property Amount $amount
 * @property null|Customer $customer
 * @property string $status See {@see PaymentIntentStatus}.
 * @property string $description
 * @property null|string $return_url
 * @property null|string $webhook_url
 * @property null|string $statement_descriptor
 * @property null|string $receipt_email
 * @property array<string,mixed> $metadata
 * @property null|PaymentAttempt $active_attempt
 * @property null|string $completed_at
 * @property null|string $expired_at When the payment was sealed as expired. A payment only expires when it was never attempted.
 * @property null|string $created_at
 */
final class PaymentIntent extends BaseResource
{
    protected static function casts(): array
    {
        return [
            'amount'         => Amount::class,
            'customer'       => Customer::class,
            'active_attempt' => PaymentAttempt::class,
        ];
    }
}
