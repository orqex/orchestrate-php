<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Resource;

/**
 * The customer attached to a payment intent.
 *
 * @property string $id Public customer id (e.g. "cus_...").
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property null|string $avatar_url
 */
final class Customer extends BaseResource {}
