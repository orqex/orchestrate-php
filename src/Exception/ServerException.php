<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Exception;

/**
 * HTTP 5xx — the API failed to process the request. These are retried
 * automatically up to the configured number of network retries.
 */
final class ServerException extends ApiException {}
