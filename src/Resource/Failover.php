<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Resource;

/**
 * Failover evaluation for a payment attempt: whether it ran on a fallback
 * gateway and, if not, why the capture did not fall back.
 *
 * @property bool $is_failover Whether this attempt was executed on a fallback gateway.
 * @property null|array<string,mixed> $decision Diagnostic detail about the retry evaluation.
 *                                              Informational only — do not branch on it.
 * @property null|string $hint Human-readable explanation of the decision.
 */
final class Failover extends BaseResource {}
