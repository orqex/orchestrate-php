<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Exception;

/**
 * HTTP 403 — the API key is valid but not allowed to perform the action.
 */
final class PermissionException extends ApiException {}
