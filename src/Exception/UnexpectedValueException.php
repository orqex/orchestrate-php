<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Exception;

/**
 * Thrown when the API returns a payload the SDK cannot decode
 * (malformed JSON or an unexpected structure).
 */
final class UnexpectedValueException extends \UnexpectedValueException implements OrchestrateException {}
