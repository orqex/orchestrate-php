<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Resource;

/**
 * A currency supported by the exchange-rate service.
 *
 * @property string $name
 * @property string $symbol
 * @property string $code
 * @property null|string $icon_url
 */
final class Currency extends BaseResource {}
