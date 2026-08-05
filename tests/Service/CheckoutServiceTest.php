<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Tests\Service;

use Orqex\Orchestrate\Enum\BorderStyle;
use Orqex\Orchestrate\Enum\CheckoutTemplate;
use Orqex\Orchestrate\Enum\FontFamily;
use Orqex\Orchestrate\Resource\Appearance;
use Orqex\Orchestrate\Resource\Checkout;
use Orqex\Orchestrate\Resource\PaymentIntent;
use Orqex\Orchestrate\Tests\Support\FakeApi;
use PHPUnit\Framework\TestCase;

final class CheckoutServiceTest extends TestCase
{
    public function test_create_flattens_the_checkout_and_payment_envelope(): void
    {
        $api = new FakeApi([FakeApi::json([
            'message' => 'Checkout session created successfully.',
            'data'    => [
                'checkout' => [
                    'id'          => 'cs_1',
                    'status'      => 'open',
                    'environment' => 'sandbox',
                    'url'         => 'https://pay.orqex.com/cs_1',
                ],
                'payment' => ['id' => 'TRX1', 'amount' => ['value' => 5000, 'currency' => 'USD']],
            ],
        ], 201)]);

        $checkout = $api->client->checkouts()->create([
            'amount'             => 50,
            'currency'           => 'USD',
            'customer'           => ['email' => 'a@b.co', 'first_name' => 'A', 'last_name' => 'B'],
            'expires_in_minutes' => 60,
        ]);

        $this->assertInstanceOf(Checkout::class, $checkout);
        $this->assertSame('cs_1', $checkout->id);
        $this->assertSame('sandbox', $checkout->environment);
        $this->assertInstanceOf(PaymentIntent::class, $checkout->payment);
        $this->assertSame('TRX1', $checkout->payment->id);
        $this->assertSame('/v1/payment/checkouts', $api->lastRequest()->getUri()->getPath());
    }

    public function test_retrieve_uses_the_correct_path(): void
    {
        $api = new FakeApi([FakeApi::json([
            'data' => ['checkout' => ['id' => 'cs_1', 'status' => 'open', 'environment' => 'live']],
        ])]);

        $checkout = $api->client->checkouts()->retrieve('cs_1');

        $this->assertSame('GET', $api->lastRequest()->getMethod());
        $this->assertSame('/v1/payment/checkouts/cs_1', $api->lastRequest()->getUri()->getPath());
        $this->assertSame('live', $checkout->environment);
    }

    public function test_retrieve_hydrates_the_resolved_appearance(): void
    {
        $api = new FakeApi([FakeApi::json([
            'data' => ['checkout' => [
                'id'          => 'cs_1',
                'status'      => 'open',
                'environment' => 'live',
                'appearance'  => [
                    'template'               => 'raven',
                    'color'                  => [
                        'primary'  => ['hex' => '#1a73e8', 'rgb' => '26, 115, 232'],
                        'contrast' => ['hex' => '#ffffff', 'rgb' => '255, 255, 255'],
                    ],
                    'font'                   => [
                        'value'        => 'inter',
                        'display_name' => 'Inter',
                        'category'     => 'Sans-serif',
                        'url'          => 'https://fonts.googleapis.com/css2?family=Inter',
                    ],
                    'border_style'           => 'rounded',
                    'display_platform_badge' => true,
                    'brand'                  => 'Acme',
                    'lang'                   => 'en',
                ],
            ]],
        ])]);

        $checkout = $api->client->checkouts()->retrieve('cs_1');

        $this->assertInstanceOf(Appearance::class, $checkout->appearance);
        $this->assertSame(CheckoutTemplate::RAVEN->value, $checkout->appearance->template);
        $this->assertSame(BorderStyle::ROUNDED->value, $checkout->appearance->border_style);
        $this->assertSame(FontFamily::INTER->value, $checkout->appearance->font['value']);
        $this->assertSame('#1a73e8', $checkout->appearance->color['primary']['hex']);
        $this->assertTrue($checkout->appearance->display_platform_badge);
        $this->assertSame('Acme', $checkout->appearance->brand);
    }
}
