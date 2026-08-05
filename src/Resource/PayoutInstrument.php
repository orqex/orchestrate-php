<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Resource;

use Orqex\Orchestrate\Enum\PayoutInstrumentType;

/**
 * The destination instrument of a payout with its full details.
 *
 * @property string $id
 * @property string $type See {@see PayoutInstrumentType}.
 * @property null|Country $country Returned as an object, although a plain ISO 3166-1 alpha-2 code is sent on the way in.
 * @property null|string $phone_number Phone number; present when type is `phone`.
 * @property null|string $account_name Account holder name; present when type is `bank_account`.
 * @property null|string $account_number Bank account number; present when type is `bank_account`.
 * @property null|string $bank_code Bank / routing code; present when type is `bank_account`.
 * @property null|string $swift_bic SWIFT/BIC code; present when type is `bank_account`.
 * @property null|string $address Crypto wallet address; present when type is `crypto_address`.
 * @property null|string $network Crypto network (e.g. "ERC20"); present when type is `crypto_address`.
 * @property null|string $memo_tag Memo or tag for crypto destinations; present when type is `crypto_address`.
 */
final class PayoutInstrument extends BaseResource
{
    protected static function casts(): array
    {
        return [
            'country' => Country::class,
        ];
    }
}
