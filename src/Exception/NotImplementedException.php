<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Exception;

/**
 * HTTP 501 — the endpoint exists but is not yet available on the API.
 *
 * No endpoint currently returns it; the mapping is kept so a future one
 * surfaces as a typed exception rather than a generic server error.
 */
final class NotImplementedException extends ApiException {}
