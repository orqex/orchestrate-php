<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Enum;

/**
 * Structured reason for a refund, selected when creating one.
 */
enum RefundReason: string
{
    case REQUESTED_BY_CUSTOMER = 'requested_by_customer';
    case DUPLICATE = 'duplicate';
    case FRAUDULENT = 'fraudulent';
    case PRODUCT_NOT_RECEIVED = 'product_not_received';
    case PRODUCT_UNSATISFACTORY = 'product_unsatisfactory';
    case ORDER_CANCELLED = 'order_cancelled';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::REQUESTED_BY_CUSTOMER  => 'Requested by customer',
            self::DUPLICATE              => 'Duplicate payment',
            self::FRAUDULENT             => 'Fraudulent',
            self::PRODUCT_NOT_RECEIVED   => 'Product not received',
            self::PRODUCT_UNSATISFACTORY => 'Product unsatisfactory',
            self::ORDER_CANCELLED        => 'Order cancelled',
            self::OTHER                  => 'Other',
        };
    }
}
