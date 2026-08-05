<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Resource;

use Orqex\Orchestrate\Enum\RequeryStatus;

/**
 * The result of re-checking a payment against its provider.
 *
 * @property string $id Public requery id (prefix "rq_").
 * @property null|string $status See {@see RequeryStatus}.
 * @property null|string $payment_intent_id
 * @property null|string $payment_attempt_id
 * @property array<string,mixed> $before Intent and attempt status before the requery.
 * @property array<string,mixed> $after Intent and attempt status after the requery.
 * @property array<string,mixed> $attempts Requeried attempt count and details.
 * @property null|string $completed_at
 * @property null|string $created_at
 */
final class Requery extends BaseResource {}
