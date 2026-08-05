<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Resource;

/**
 * The record the underlying provider holds for a payment attempt or a payout.
 *
 * Intended for support and debugging. `payload` is passed through unchanged,
 * so its shape is provider-specific and unstable: read it, do not build
 * production logic on it.
 *
 * @property null|string $gateway
 * @property null|string $gateway_transaction_id
 * @property null|string $retrieved_at
 * @property array<string,mixed> $payload Unmodified provider payload.
 */
final class GatewayInspection extends BaseResource {}
