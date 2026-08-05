<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Enum;

/**
 * The kind of next action a client must perform to advance an attempt.
 */
enum NextActionType: string
{
    case REDIRECT_TO_URL = 'redirect_to_url';
    case EMBED_IFRAME = 'embed_iframe';
    case COLLECT_OTP = 'collect_otp';
    case APPROVE_ON_PHONE = 'approve_on_phone';
    case SCAN_QR_CODE = 'scan_qr_code';
    case DISPLAY_PAYMENT_INSTRUCTIONS = 'display_payment_instructions';
    case COMPLETE_WITH_SDK = 'complete_with_sdk';
    case NONE = 'none';

    public function label(): string
    {
        return match ($this) {
            self::REDIRECT_TO_URL              => 'Redirect to URL',
            self::EMBED_IFRAME                 => 'Embed iframe',
            self::COLLECT_OTP                  => 'Collect OTP',
            self::APPROVE_ON_PHONE             => 'Approve on phone',
            self::SCAN_QR_CODE                 => 'Scan QR code',
            self::DISPLAY_PAYMENT_INSTRUCTIONS => 'Display payment instructions',
            self::COMPLETE_WITH_SDK            => 'Complete with SDK',
            self::NONE                         => 'None',
        };
    }
}
