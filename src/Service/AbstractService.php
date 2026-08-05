<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Service;

use Orqex\Orchestrate\Exception\InvalidArgumentException;
use Orqex\Orchestrate\Http\ApiClient;
use Orqex\Orchestrate\Http\ApiResponse;
use Orqex\Orchestrate\Http\RequestOptions;
use Orqex\Orchestrate\Resource\BaseResource;
use Orqex\Orchestrate\Resource\Collection;

/**
 * Base class for API services. Services are thin, stateless wrappers that
 * translate method calls into HTTP requests and hydrate the responses into
 * typed resources.
 */
abstract class AbstractService
{
    public function __construct(protected readonly ApiClient $client) {}

    /**
     * Interpolate path identifiers into a sprintf template, url-encoding each
     * and rejecting empty ones.
     */
    protected function buildPath(string $template, string ...$ids): string
    {
        $encoded = array_map(
            static function (string $id): string {
                if (trim($id) === '') {
                    throw new InvalidArgumentException('A resource id is required and cannot be empty.');
                }

                return rawurlencode($id);
            },
            $ids,
        );

        return vsprintf($template, $encoded);
    }

    /**
     * Send a request and hydrate the `data` envelope into a typed resource.
     *
     * @template T of BaseResource
     *
     * @param class-string<T> $resourceClass
     * @param array<string, mixed> $params
     * @param null|array<string,mixed>|RequestOptions $opts
     *
     * @return T
     */
    protected function requestResource(
        string $resourceClass,
        string $method,
        string $path,
        array $params = [],
        null|array|RequestOptions $opts = null,
    ): BaseResource {
        $response = $this->client->request($method, $path, $params, $opts);

        return $resourceClass::constructFrom($this->dataOf($response), $response);
    }

    /**
     * Send a GET request and hydrate a cursor-paginated collection.
     *
     * @template T of BaseResource
     *
     * @param class-string<T> $resourceClass
     * @param array<string, mixed> $params
     * @param null|array<string,mixed>|RequestOptions $opts
     *
     * @return Collection<T>
     */
    protected function requestCollection(
        string $resourceClass,
        string $path,
        array $params = [],
        null|array|RequestOptions $opts = null,
    ): Collection {
        $response = $this->client->request('GET', $path, $params, $opts);

        $rows = $response->json['data'] ?? null;
        $pagination = $response->json['pagination'] ?? null;

        $items = [];

        if (is_array($rows)) {
            foreach ($rows as $row) {
                if (is_array($row)) {
                    $items[] = $resourceClass::constructFrom($row, $response);
                }
            }
        }

        return new Collection(
            $items,
            is_array($pagination) ? $pagination : [],
            $response,
            fn (array $nextParams): Collection => $this->requestCollection($resourceClass, $path, $nextParams, $opts),
        );
    }

    /**
     * Escape hatch returning the raw, un-hydrated response.
     *
     * @param array<string, mixed> $params
     * @param null|array<string,mixed>|RequestOptions $opts
     */
    protected function requestRaw(
        string $method,
        string $path,
        array $params = [],
        null|array|RequestOptions $opts = null,
    ): ApiResponse {
        return $this->client->request($method, $path, $params, $opts);
    }

    /**
     * @return array<string, mixed>
     */
    private function dataOf(ApiResponse $response): array
    {
        if (is_array($response->json['data'] ?? null)) {
            return $response->json['data'];
        }

        return $response->json;
    }
}
