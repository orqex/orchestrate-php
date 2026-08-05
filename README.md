# Orchestrate PHP

Official PHP SDK for the [Orqex](https://orqex.com) payment orchestration API.

A single, framework-agnostic client for the Orqex merchant API: create payment intents,
run hosted checkouts, attempt and confirm payments, issue refunds, requery transactions
and read exchange rates. Pure PHP, with an optional Laravel integration that activates
only inside Laravel applications.

- **Pure PHP 8.3+** — works in any project, no framework required.
- **Typed resources** with IDE autocompletion, forward-compatible with new API fields.
- **Guzzle by default**, or bring your own HTTP client.
- **Automatic retries** with exponential backoff and jitter.
- **Idempotency keys** generated automatically for every write.
- **Cursor pagination** with a transparent auto-paging iterator.
- **Typed exceptions** mapped from HTTP status codes.

## Documentation

Full documentation lives at **[docs.orqex.com](https://docs.orqex.com)**:

| Guide | Link |
|---|---|
| Installation & configuration | https://docs.orqex.com/sdk/php/installation |
| Usage (services, resources, errors) | https://docs.orqex.com/sdk/php/usage |
| Laravel integration | https://docs.orqex.com/sdk/php/laravel |
| API reference | https://docs.orqex.com/api-reference |
| API conventions | https://docs.orqex.com/api-conventions |

## Installation

```bash
composer require orqex/orchestrate-php
```

Requires PHP 8.3+ and `ext-json`. Guzzle 7.5+ is installed automatically.

## Quickstart

```php
use Orqex\Orchestrate\OrchestrateClient;

$orqex = new OrchestrateClient('sk_live_xxx');

$intent = $orqex->paymentIntents()->create([
    'amount'      => 50,
    'currency'    => 'USD',
    'description' => 'Order #1024',
    'customer'    => [
        'email'      => 'ama@example.com',
        'first_name' => 'Ama',
        'last_name'  => 'Mensah',
    ],
]);

echo $intent->status;          // "pending"
echo $intent->amount->value;   // 50 (major units)
```

Start a payment, resolve the customer's next action, then read the final status — see the
[usage guide](https://docs.orqex.com/sdk/php/usage) for the full flow.

### Laravel

The package ships an auto-discovered service provider and `Orchestrate` facade. Set
`ORCHESTRATE_SECRET_KEY` in `.env`, then:

```php
use Orqex\Orchestrate\Laravel\Facades\Orchestrate;

Orchestrate::paymentIntents()->all();
```

See the [Laravel guide](https://docs.orqex.com/sdk/php/laravel).

## Development

```bash
composer test       # PHPUnit
composer analyse    # PHPStan
composer sniff      # php-cs-fixer (dry run)
composer format     # php-cs-fixer (apply)
```

## License

Proprietary. Copyright Orqex. See [LICENSE](LICENSE).
