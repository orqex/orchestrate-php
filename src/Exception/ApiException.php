<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Exception;

/**
 * Base class for every error returned by the Orqex API (any 4xx/5xx response).
 *
 * Carries the decoded error envelope so callers can inspect the HTTP status,
 * the per-field validation errors, the request id and the raw body.
 */
class ApiException extends \RuntimeException implements OrchestrateException
{
    /**
     * @param array<string, array<int, string>> $errors per-field validation errors keyed by field name
     * @param null|array<string, mixed> $rawBody the decoded response body, when available
     */
    public function __construct(
        string $message,
        public readonly int $httpStatus = 0,
        public readonly array $errors = [],
        public readonly ?string $requestId = null,
        public readonly ?array $rawBody = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    /**
     * Build the most specific exception subclass for a given HTTP status code.
     *
     * @param array<string, array<int, string>> $errors
     * @param null|array<string, mixed> $rawBody
     */
    public static function fromHttpStatus(
        int $httpStatus,
        string $message,
        array $errors = [],
        ?string $requestId = null,
        ?array $rawBody = null,
    ): self {
        $class = match (true) {
            $httpStatus === 401                       => AuthenticationException::class,
            $httpStatus === 403                       => PermissionException::class,
            $httpStatus === 404                       => NotFoundException::class,
            $httpStatus === 422, $httpStatus === 400  => InvalidRequestException::class,
            $httpStatus === 428, $httpStatus === 409  => IdempotencyException::class,
            $httpStatus === 429                       => RateLimitException::class,
            $httpStatus === 501                       => NotImplementedException::class,
            $httpStatus >= 500                        => ServerException::class,
            default                                   => self::class,
        };

        return new $class($message, $httpStatus, $errors, $requestId, $rawBody);
    }

    /**
     * The first validation error message, if any.
     */
    public function firstError(): ?string
    {
        foreach ($this->errors as $messages) {
            if (isset($messages[0])) {
                return $messages[0];
            }
        }

        return null;
    }
}
