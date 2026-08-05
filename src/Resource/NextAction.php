<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Resource;

use Orqex\Orchestrate\Enum\NextActionType;

/**
 * The next action a client must perform to advance a payment attempt.
 *
 * The shape is polymorphic and driven by `type`. See
 * {@see NextActionType} for the full list. Read the
 * type, then the fields relevant to it (e.g. `url` for `redirect_to_url`,
 * `fields` for `collect_otp`, `content`/`image_url` for `scan_qr_code`).
 *
 * For `approve_on_phone` and `collect_otp`, the USSD shortcode the customer
 * dials is exposed as `dial_code` (not `code`).
 *
 * @property string $type One of the NextActionType values.
 * @property null|string $dial_code USSD shortcode for approve_on_phone / collect_otp next actions.
 */
final class NextAction extends BaseResource {}
