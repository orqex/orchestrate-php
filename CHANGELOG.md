# Changelog

All notable changes to this project are documented here.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

While the SDK is on the `0.x` line, a breaking change bumps the minor version.

## [0.1.1](https://github.com/orqex/orchestrate-php/compare/v0.1.0...v0.1.1) (2026-08-05)


### Bug Fixes

* sync payment resources with the lifecycle timestamps ([#2](https://github.com/orqex/orchestrate-php/issues/2)) ([afda3f8](https://github.com/orqex/orchestrate-php/commit/afda3f828a08a9d276bb11f2f668b73e482b0bee))

## 0.1.0

First public release.

### Added

- `OrchestrateClient` with services for payment intents, hosted checkouts, payment attempts,
  refunds, payouts, requery, exchange rates and payment method status.
- Pure-PHP core over a pluggable HTTP transport, Guzzle by default, with any
  `HttpClientInterface` implementation accepted.
- Automatic idempotency keys on writes, and retries with exponential backoff and full jitter
  on connection errors, `429` and `5xx`.
- Typed, forward-compatible resources: unknown fields returned by a newer API version are
  preserved rather than dropped.
- Cursor pagination with an auto-paging iterator.
- Typed exception hierarchy mapped from HTTP status codes.
- Optional Laravel service provider and `Orchestrate` facade, auto-discovered.
- The secret key is masked in `var_dump`, `print_r` and `dd()`, and the configuration refuses
  to serialise so a queued or cached payload cannot carry it.
