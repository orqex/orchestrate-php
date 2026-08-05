<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Exception;

/**
 * Thrown when the SDK cannot reach the Orqex API at all
 * (DNS failure, TLS error, connection refused, timeout).
 *
 * These errors are transient by nature; the SDK retries them
 * automatically up to the configured number of network retries.
 */
final class ApiConnectionException extends \RuntimeException implements OrchestrateException {}
