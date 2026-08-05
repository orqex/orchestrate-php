<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Exception;

/**
 * HTTP 404 — the requested resource does not exist or is not visible
 * to the authenticated project.
 */
final class NotFoundException extends ApiException {}
