<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Tests\Unit\Resource;

use Orqex\Orchestrate\Resource\Amount;
use Orqex\Orchestrate\Resource\Customer;
use Orqex\Orchestrate\Resource\PaymentIntent;
use PHPUnit\Framework\TestCase;

final class BaseResourceTest extends TestCase
{
    public function test_it_exposes_attributes_as_properties_and_array_access(): void
    {
        $intent = PaymentIntent::constructFrom(['id' => 'TRX1', 'description' => 'Order #1']);

        $this->assertSame('TRX1', $intent->id);
        $this->assertSame('TRX1', $intent['id']);
        $this->assertTrue(isset($intent->description));
        $this->assertNull($intent->unknown_field);
    }

    public function test_it_casts_nested_objects(): void
    {
        $intent = PaymentIntent::constructFrom([
            'id'       => 'TRX1',
            'amount'   => ['value' => 5000, 'currency' => 'USD'],
            'customer' => ['id' => 'cus_1', 'email' => 'a@b.co'],
        ]);

        $this->assertInstanceOf(Amount::class, $intent->amount);
        $this->assertSame(5000, $intent->amount->value);
        $this->assertInstanceOf(Customer::class, $intent->customer);
        $this->assertSame('a@b.co', $intent->customer->email);
    }

    public function test_it_preserves_unknown_forward_compatible_fields(): void
    {
        $intent = PaymentIntent::constructFrom(['id' => 'TRX1', 'brand_new_field' => 'kept']);

        $this->assertSame('kept', $intent->brand_new_field);
        $this->assertArrayHasKey('brand_new_field', $intent->toArray());
    }

    public function test_to_array_unwraps_nested_resources(): void
    {
        $intent = PaymentIntent::constructFrom([
            'id'     => 'TRX1',
            'amount' => ['value' => 5000, 'currency' => 'USD'],
        ]);

        $array = $intent->toArray();
        $this->assertIsArray($array['amount']);
        $this->assertSame(5000, $array['amount']['value']);
        $this->assertSame($array, json_decode((string) json_encode($intent), true));
    }
}
