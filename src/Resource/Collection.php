<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Resource;

use Orqex\Orchestrate\Http\ApiResponse;

/**
 * A single page of a cursor-paginated list.
 *
 * Iterate the current page directly, or walk every page transparently with
 * {@see Collection::autoPagingIterator()} which follows the cursor for you.
 *
 * @template T of BaseResource
 *
 * @implements \IteratorAggregate<int, T>
 */
final class Collection implements \IteratorAggregate, \Countable, \JsonSerializable
{
    /**
     * @param array<int, T> $data
     * @param array<string, mixed> $pagination
     * @param \Closure $pageFetcher fetches the next page given its parsed query params
     */
    public function __construct(
        public readonly array $data,
        public readonly array $pagination,
        private readonly ApiResponse $lastResponse,
        private readonly \Closure $pageFetcher,
    ) {}

    public function hasMore(): bool
    {
        return (bool) ($this->pagination['has_more_pages'] ?? false);
    }

    /**
     * Fetch the next page, or null when there are no more pages.
     *
     * @return null|self<T>
     */
    public function nextPage(): ?self
    {
        $nextUrl = $this->pagination['next_page_url'] ?? null;

        if (! is_string($nextUrl) || $nextUrl === '') {
            return null;
        }

        $query = (string) (parse_url($nextUrl, PHP_URL_QUERY) ?: '');
        parse_str($query, $params);

        return ($this->pageFetcher)($params);
    }

    /**
     * Yield every resource across all pages, fetching pages on demand.
     *
     * @return \Generator<int, T>
     */
    public function autoPagingIterator(): \Generator
    {
        $page = $this;

        while (true) {
            foreach ($page->data as $item) {
                yield $item;
            }

            $next = $page->nextPage();

            if ($next === null) {
                break;
            }

            $page = $next;
        }
    }

    public function lastResponse(): ApiResponse
    {
        return $this->lastResponse;
    }

    /**
     * @return \ArrayIterator<int, T>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->data);
    }

    public function count(): int
    {
        return count($this->data);
    }

    /**
     * @return array{data: array<int, T>, pagination: array<string, mixed>}
     */
    public function jsonSerialize(): array
    {
        return [
            'data'       => $this->data,
            'pagination' => $this->pagination,
        ];
    }
}
