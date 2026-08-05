<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Exception;

/**
 * HTTP 428/409 — an idempotency-key problem: the key is required but
 * missing, has an invalid length, or conflicts with a previous request.
 */
final class IdempotencyException extends ApiException {}
