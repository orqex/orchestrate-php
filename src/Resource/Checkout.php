<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Resource;

use Orqex\Orchestrate\Enum\CheckoutStatus;

/**
 * A hosted checkout session.
 *
 * @property string $id Public checkout id (e.g. "cs_...").
 * @property string $status See {@see CheckoutStatus}.
 * @property string $environment The environment for this session ("live" or "sandbox").
 * @property string $url Hosted checkout URL to redirect the customer to.
 * @property null|array<string,mixed> $restriction
 * @property null|Appearance $appearance Resolved checkout appearance (project base + per-session overrides).
 * @property null|string $expires_at
 * @property null|string $created_at
 * @property null|PaymentIntent $payment The payment intent bound to this session.
 */
final class Checkout extends BaseResource
{
    protected static function casts(): array
    {
        return [
            'appearance' => Appearance::class,
            'payment'    => PaymentIntent::class,
        ];
    }
}
