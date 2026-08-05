<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Exception;

/**
 * HTTP 429 — too many requests. Back off and retry after the delay
 * advertised in the `Retry-After` response header.
 */
final class RateLimitException extends ApiException {}
