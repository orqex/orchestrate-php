<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Resource;

/**
 * A country available for payment on a payment intent.
 *
 * @property string $code ISO 3166-1 alpha-2 country code (e.g. "DE").
 * @property string $name Localised display name (e.g. "Benin").
 * @property null|string $flag Flag icon URL.
 */
final class Country extends BaseResource {}
