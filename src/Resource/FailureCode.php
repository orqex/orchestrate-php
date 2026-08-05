<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Resource;

use Orqex\Orchestrate\Enum\FailureCategory;

/**
 * Structured failure code surfaced on a failed payment attempt.
 *
 * @property string $value Machine-readable error code (e.g. "ROUTING_NOT_CONFIGURED").
 * @property string $category One of the {@see FailureCategory} values.
 * @property null|string $message Human-readable merchant-facing explanation.
 */
final class FailureCode extends BaseResource {}
