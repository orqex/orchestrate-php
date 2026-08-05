<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Resource;

/**
 * The result of a "available countries" call on a payment intent.
 *
 * Wraps the list of countries together with the envelope metadata.
 *
 * @property list<Country> $data Hydrated country objects.
 * @property bool $supports_any_country Whether the intent accepts any country.
 * @property int $total Total number of available countries.
 */
final class CountryList extends BaseResource
{
    /** @return list<Country> */
    public function countries(): array
    {
        return $this->attributes['data'] ?? [];
    }

    protected static function casts(): array
    {
        return [
            'data' => [Country::class],
        ];
    }
}
