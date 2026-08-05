<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Enum;

/**
 * Broad category for a payment attempt failure code.
 */
enum FailureCategory: string
{
    /** Provider-side failure or rejection (outage, 5xx, gateway limits). */
    case GATEWAY_ERROR = 'GATEWAY_ERROR';

    /** Customer-fixable causes (wrong card, insufficient balance, cancellation). */
    case CUSTOMER_ERROR = 'CUSTOMER_ERROR';

    /** Mobile network operator failures. */
    case TELCO_ERROR = 'TELCO_ERROR';

    /** Project misconfiguration (routing, gateway credentials). */
    case MERCHANT_CONFIG_ERROR = 'MERCHANT_CONFIG_ERROR';

    /** Platform faults — bugs or invalid requests on our side. */
    case PLATFORM_ERROR = 'PLATFORM_ERROR';

    /** Integrity and fraud safety blocks. */
    case SECURITY_ERROR = 'SECURITY_ERROR';
}
