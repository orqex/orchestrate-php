<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Resource;

/**
 * A payment method descriptor as embedded in a payment attempt.
 *
 * @property string $value Method code to send as `method_code`. Discovered at runtime, never hard-coded.
 * @property string $label Display label (e.g. "Test method").
 * @property string $description Localised description.
 * @property string $icon_url Method icon URL.
 * @property string $category Broad family the method belongs to.
 * @property bool $requires_phone Whether a payer phone number is required for this method.
 */
final class PaymentMethod extends BaseResource {}
