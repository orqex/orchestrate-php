<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Exception;

/**
 * Base contract implemented by every exception thrown by the SDK.
 *
 * Catch this interface to handle any Orchestrate error in a single block.
 */
interface OrchestrateException extends \Throwable {}
