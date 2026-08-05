<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Resource;

use Orqex\Orchestrate\Enum\BorderStyle;
use Orqex\Orchestrate\Enum\CheckoutTemplate;
use Orqex\Orchestrate\Enum\FontFamily;

/**
 * Resolved appearance for a hosted checkout session.
 *
 * The project's appearance settings act as the base; any field supplied at
 * checkout creation overrides them for that session only.
 *
 * @property string $template Visual template. See {@see CheckoutTemplate}.
 * @property array{primary: array{hex: string, rgb: string}, contrast: array{hex: string, rgb: string}} $color Primary colour and its computed contrast colour, each as hex and "r, g, b" strings.
 * @property array{value: string, display_name: string, category: string, url: null|string} $font Resolved font; `value` maps to {@see FontFamily}.
 * @property string $border_style Border style. See {@see BorderStyle}.
 * @property bool $display_platform_badge Whether the "powered by" badge is shown.
 * @property null|string $brand Brand label rendered on the checkout (defaults to the project name).
 * @property null|string $lang Checkout language ("en" or "fr").
 */
final class Appearance extends BaseResource {}
