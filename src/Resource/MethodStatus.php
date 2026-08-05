<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Resource;

use Orqex\Orchestrate\Enum\PaymentMethodStatus;

/**
 * Operational health of a payment method in a country, over a recent window.
 *
 * @property string $method The method code the status was computed for.
 * @property string $country ISO 3166-1 alpha-2 country code.
 * @property string $status See {@see PaymentMethodStatus}.
 */
final class MethodStatus extends BaseResource {}
