<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Resource;

/**
 * A monetary amount as returned by the API.
 *
 * @property float|int $value Amount in major units: 50 means 50.00. Whole amounts arrive as
 *                            an int because JSON drops the zero fraction, so compare
 *                            loosely or cast before formatting.
 * @property string $formatted Human-readable amount with currency (e.g. "$50.00").
 * @property string $short Compact human-readable amount (e.g. "$50").
 * @property string $currency ISO 4217 currency code (e.g. "USD").
 */
final class Amount extends BaseResource {}
