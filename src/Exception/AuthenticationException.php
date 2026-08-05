<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Exception;

/**
 * HTTP 401 — the API key is missing, malformed, inactive or expired.
 */
final class AuthenticationException extends ApiException {}
