<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Exception;

/**
 * Thrown when the SDK is misconfigured or called with invalid arguments
 * before any request leaves the process (e.g. a missing API key).
 */
final class InvalidArgumentException extends \InvalidArgumentException implements OrchestrateException {}
