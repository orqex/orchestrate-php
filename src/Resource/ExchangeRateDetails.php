<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Resource;

/**
 * An effective currency conversion returned with a transaction.
 *
 * @property string $value Decimal exchange rate, with up to eight decimal places.
 * @property string $from_currency Source currency code.
 * @property string $to_currency Destination currency code.
 * @property string $expression Human-readable conversion expression.
 */
final class ExchangeRateDetails extends BaseResource {}
