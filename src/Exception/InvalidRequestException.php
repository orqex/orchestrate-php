<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Exception;

/**
 * HTTP 400/422 — the request was rejected by validation.
 *
 * Inspect {@see ApiException::$errors} for the per-field messages.
 */
final class InvalidRequestException extends ApiException {}
