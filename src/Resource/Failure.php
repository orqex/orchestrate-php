<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Resource;

/**
 * Failure details on a payment attempt.
 *
 * @property null|FailureCode $code Structured failure code with value, category and message.
 * @property null|string $message Deprecated top-level message; prefer {@see FailureCode::$message}.
 */
final class Failure extends BaseResource
{
    protected static function casts(): array
    {
        return [
            'code' => FailureCode::class,
        ];
    }
}
