# CLAUDE.md

This file guides Claude Code (claude.ai/code) when working in this repository.

## Project Overview

`orqex/orchestrate-php` is the official **pure-PHP SDK** for the Orqex payment orchestration API (the merchant-facing public API at `https://api.orqex.com/v1`, authenticated with an `sk_` Bearer key). It mirrors the endpoints exposed by the Orqex backend's `routes/api/public/v1.php`.

The SDK is framework-agnostic. A thin, optional Laravel integration (service provider + facade) auto-activates only inside Laravel applications and adds **no** hard dependency on the framework.

## Key Commands

```bash
composer test       # PHPUnit suite (Unit, Service, Laravel testsuites)
composer analyse    # PHPStan (level 6)
composer sniff      # php-cs-fixer dry-run (axazara/php-cs config)
composer format     # php-cs-fixer apply
```

Run a single test: `vendor/bin/phpunit --filter=test_name`.

## Architecture

Layered, Stripe-inspired, adapted to the Orqex API:

```
OrchestrateClient                 public facade; lazy, memoised service accessors
  -> Service\*Service             stateless wrappers; one per resource group
       -> Http\ApiClient          builds/sends/interprets requests; auth, idempotency,
                                  retries (exp. backoff + jitter), error mapping
            -> Contract\HttpClientInterface   transport abstraction
                 -> Http\GuzzleHttpClient      default transport (Guzzle)
```

- **`OrchestrateClient`** — entry point. Services are exposed as methods (`paymentIntents()`, `checkouts()`, `refunds()`, `attempts()`, `reconciliations()`, `exchangeRates()`), memoised. Methods (not properties) so the Laravel facade can forward them.
- **`ClientConfiguration`** — validates the `sk_` key, resolves the HTTP client (custom `HttpClientInterface`, a Guzzle `ClientInterface`, or the default).
- **`Http\ApiClient`** — the API-level client (Stripe's `ApiRequestor` + `BaseStripeClient` role). Retry logic lives **here**, not in the transport, so any injected transport inherits retries. Generates an idempotency key once per request and reuses it across retries.
- **`Service\AbstractService`** — `buildPath()` (urlencodes + rejects empty ids), `requestResource()` (hydrate single `data`), `requestCollection()` (hydrate cursor page), `requestRaw()` (escape hatch).
- **`Resource\BaseResource`** — typed DTOs. `ArrayAccess` + magic props + `@property` PHPDoc for IDE autocompletion. `casts()` declares nested-object hydration. Resources are **read-only** and **forward-compatible** (unknown fields are preserved).
- **`Resource\Collection`** — one cursor page; `nextPage()` follows `pagination.next_page_url`; `autoPagingIterator()` walks all pages lazily.
- **`Exception\*`** — `OrchestrateException` (interface) is the catch-all. `ApiException::fromHttpStatus()` maps status codes to subclasses.
- **`Enum\*`** — type-safe constants mirrored from the Orqex backend enums.
- **`Laravel\*`** — optional `OrchestrateServiceProvider` + `Facades\Orchestrate`. References `Illuminate\*` (a `suggest`/dev dependency); only loaded inside Laravel via package discovery.

## Design decisions (and why)

- **Hydration by known service + `casts()`**, not a Stripe-style `object`→class map: Orqex responses carry **no `object` discriminator**, so the calling service determines the target class.
- **Read-only DTOs**, no dirty-tracking / `save()`: the Orqex API takes explicit param arrays per action, not a mutate-then-save model. Dirty-tracking would be dead complexity.
- **Retries in `ApiClient`**, not the transport: a custom HTTP client gets retries for free.

## Conventions

- PSR-12, `declare(strict_types=1)` everywhere, explicit return types, constructor property promotion.
- `final` classes for services, client, config, transport, exceptions (subclasses), resources.
- Code style is enforced by **`axazara/php-cs`** (`.php-cs-fixer.dist.php`). Run `composer format` before committing.
- Enum case names use **SCREAMING_CASE**; backing values match the API strings.
- Minimal comments; PHPDoc over inline comments.
- British English in prose where it occurs.

## Catalogue enums are deliberately absent

This package is public. `PaymentMethodCode`, `PayoutMethodCode`, `PaymentMethodCategory` and
`FailoverDecision` were removed and must not come back: between them they published the full
payment and payout method catalogues, the provider brand names inside those codes, and the
internal failover decision tree. The public documentation was rewritten to hide exactly that,
and a typed, versioned, Packagist-indexed enum is a far more convenient leak than a docs page.

Nothing needed them at runtime — they appeared only in `@property` docblocks, while the
resources hold these fields as plain strings. Method codes are discovered per payment through
`paymentIntents()->availableMethods()`; payout method codes come from the merchant's
dashboard. Document them as strings and leave it there.

Status and shape enums are fine to mirror: statuses, next-action types, failure categories,
refund reasons, instrument types, checkout templates, fonts. All of those are published in the
documentation already.

## Never leak the secret key

`ClientConfiguration` masks `apiKey` through `__debugInfo()` and refuses `__serialize()`,
because a forgotten `dd($client)` in a Laravel app renders to the browser and can reach logs
and error trackers. `SecretMaskingTest` pins this. Do not add a getter, a `__toString`, or a
log line that prints the raw key — use `maskedApiKey()`.

## Critical constraints

- **Never invent enum values, field names, paths or response shapes.** Everything must mirror the Orqex backend (`orqex/orchestrate-api`, `app/Http/Controllers/PublicApi/V1/`, `app/Http/Resources/PublicApi/`, `app/Enums/Payment/`). When adding endpoints, read the controller, its FormRequest and its API Resource first.
- **Every change is tested.** Add or update a test (Guzzle `MockHandler` via `tests/Support/FakeApi`), then run the affected tests.
- **Keep `Collection` invariant-generic.** It is a pure value object; the page fetcher is a bare `\Closure` to avoid generic-variance issues.
- Amounts are in **major units** on every write, payouts included. `fee_amount` on a payout
  is the exception: a plain integer in minor units.
- No endpoint returns 501 today. `NotImplementedException` stays mapped so a future one
  surfaces as a typed exception.

## Adding a new endpoint

1. Read the backend controller + FormRequest + Resource.
2. Add/extend the `Service` method (use `buildPath`, `requestResource`/`requestCollection`).
3. Add a typed `Resource` (with `casts()` for nested objects) if the shape is new.
4. Mirror any new enum exactly from the backend — **except catalogue enums**, see below.
5. Write a `FakeApi`-backed test asserting the path, method and hydration.
6. `composer analyse && composer sniff && composer test`, then repeat the last two against
   **PHP 8.3** — it is the declared floor and CI tests it:

   ```bash
   php83 vendor/bin/php-cs-fixer fix --dry-run && php83 vendor/bin/phpunit
   ```

   Local PHP is 8.4, so 8.4-only syntax such as `new Foo()->bar()` passes locally and fails
   the 8.3 job. Parenthesise: `(new Foo())->bar()`.

## Testing

- `tests/Unit` — configuration, HTTP client (headers, retries, error mapping, idempotency), resources, collection.
- `tests/Service` — one file per service group; assert path/method via `FakeApi::lastRequest()` and hydration of the returned resource.
- `tests/Laravel` — `orchestra/testbench`: container binding, facade, config.
- `tests/Support/FakeApi` — builds an `OrchestrateClient` over a Guzzle `MockHandler`, records requests, disables retry sleeps.
