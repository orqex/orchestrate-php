<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Resource;

use Orqex\Orchestrate\Http\ApiResponse;

/**
 * Base class for every typed API resource.
 *
 * Attributes are accessible as read-only object properties (with IDE
 * autocompletion via the `@property` annotations on each subclass) and
 * via array syntax. Unknown attributes returned by newer API versions are
 * preserved verbatim, so the SDK never drops forward-compatible fields.
 *
 * @implements \ArrayAccess<string, mixed>
 */
abstract class BaseResource implements \ArrayAccess, \JsonSerializable
{
    /**
     * @var array<string, mixed>
     */
    protected array $attributes = [];

    private ?ApiResponse $lastResponse = null;

    /**
     * @param array<string, mixed> $attributes
     */
    final public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    public function __get(string $name): mixed
    {
        return $this->attributes[$name] ?? null;
    }

    public function __isset(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    /**
     * Hydrate a resource from a decoded payload, casting nested objects.
     *
     * @param array<string, mixed> $data
     */
    public static function constructFrom(array $data, ?ApiResponse $response = null): static
    {
        $casts = static::casts();

        foreach ($casts as $key => $cast) {
            if (! array_key_exists($key, $data) || $data[$key] === null) {
                continue;
            }

            $data[$key] = self::applyCast($cast, $data[$key]);
        }

        $instance = new static($data);
        $instance->lastResponse = $response;

        return $instance;
    }

    public function lastResponse(): ?ApiResponse
    {
        return $this->lastResponse;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_map(
            static function (mixed $value): mixed {
                if ($value instanceof BaseResource) {
                    return $value->toArray();
                }

                if (is_array($value)) {
                    return array_map(
                        static fn (mixed $item): mixed => $item instanceof BaseResource ? $item->toArray() : $item,
                        $value,
                    );
                }

                return $value;
            },
            $this->attributes,
        );
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->attributes[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->attributes[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset === null) {
            return;
        }

        $this->attributes[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->attributes[$offset]);
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Map of attribute key to the resource class it should hydrate into.
     * Wrap the class in a single-element array to cast a list of objects.
     *
     * @return array<string, array{0: class-string<BaseResource>}|class-string<BaseResource>>
     */
    protected static function casts(): array
    {
        return [];
    }

    /**
     * @param array{0: class-string<BaseResource>}|class-string<BaseResource> $cast
     */
    private static function applyCast(array|string $cast, mixed $value): mixed
    {
        if (is_array($cast)) {
            $class = $cast[0];

            if (! is_array($value)) {
                return $value;
            }

            return array_map(
                static fn (mixed $item): mixed => is_array($item) ? $class::constructFrom($item) : $item,
                $value,
            );
        }

        return is_array($value) ? $cast::constructFrom($value) : $value;
    }
}
