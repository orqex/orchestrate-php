<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Tests\Service;

use Orqex\Orchestrate\Resource\Amount;
use Orqex\Orchestrate\Resource\Collection;
use Orqex\Orchestrate\Resource\Country;
use Orqex\Orchestrate\Resource\CountryList;
use Orqex\Orchestrate\Resource\Customer;
use Orqex\Orchestrate\Resource\PaymentAttempt;
use Orqex\Orchestrate\Resource\PaymentIntent;
use Orqex\Orchestrate\Resource\PaymentMethod;
use Orqex\Orchestrate\Tests\Support\FakeApi;
use PHPUnit\Framework\TestCase;

final class PaymentIntentServiceTest extends TestCase
{
    public function test_create_returns_a_hydrated_payment_intent(): void
    {
        $api = new FakeApi([FakeApi::json([
            'message' => 'Payment created.',
            'data'    => [
                'id'       => 'TRX1',
                'amount'   => ['value' => 5000, 'currency' => 'USD'],
                'customer' => ['id' => 'cus_1', 'email' => 'a@b.co'],
                'status'   => 'pending',
            ],
        ], 201)]);

        $intent = $api->client->paymentIntents()->create([
            'amount'   => 50,
            'currency' => 'USD',
            'customer' => ['email' => 'a@b.co', 'first_name' => 'A', 'last_name' => 'B'],
        ]);

        $this->assertInstanceOf(PaymentIntent::class, $intent);
        $this->assertSame('TRX1', $intent->id);
        $this->assertInstanceOf(Amount::class, $intent->amount);
        $this->assertSame(5000, $intent->amount->value);
        $this->assertInstanceOf(Customer::class, $intent->customer);

        $request = $api->lastRequest();
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/v1/payment/intents', $request->getUri()->getPath());
    }

    public function test_retrieve_uses_the_correct_path(): void
    {
        $api = new FakeApi([FakeApi::json(['data' => ['id' => 'TRX1']])]);

        $api->client->paymentIntents()->retrieve('TRX1');

        $this->assertSame('/v1/payment/intents/TRX1', $api->lastRequest()->getUri()->getPath());
        $this->assertSame('GET', $api->lastRequest()->getMethod());
    }

    public function test_active_attempt_is_hydrated_as_payment_attempt(): void
    {
        $api = new FakeApi([FakeApi::json([
            'data' => [
                'id'             => 'TRX1',
                'status'         => 'processing',
                'active_attempt' => [
                    'id'     => 'pa_1',
                    'status' => 'processing',
                ],
            ],
        ])]);

        $intent = $api->client->paymentIntents()->retrieve('TRX1');

        $this->assertInstanceOf(PaymentAttempt::class, $intent->active_attempt);
        $this->assertSame('pa_1', $intent->active_attempt->id);
    }

    public function test_available_countries_returns_hydrated_country_list(): void
    {
        $api = new FakeApi([FakeApi::json([
            'data' => [
                ['code' => 'DE', 'name' => 'Benin', 'flag' => 'https://cdn.axazara.com/flags/svg/BJ.svg'],
                ['code' => 'FR', 'name' => 'Senegal', 'flag' => 'https://cdn.axazara.com/flags/svg/SN.svg'],
            ],
            'meta' => ['total' => 2, 'supports_any_country' => false],
        ])]);

        $result = $api->client->paymentIntents()->availableCountries('TRX1');

        $this->assertInstanceOf(CountryList::class, $result);
        $this->assertSame(2, $result->total);
        $this->assertFalse($result->supports_any_country);

        $countries = $result->countries();
        $this->assertCount(2, $countries);
        $this->assertInstanceOf(Country::class, $countries[0]);
        $this->assertSame('DE', $countries[0]->code);
        $this->assertSame('Benin', $countries[0]->name);
        $this->assertSame('https://cdn.axazara.com/flags/svg/BJ.svg', $countries[0]->flag);

        $request = $api->lastRequest();
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/v1/payment/intents/TRX1/countries', $request->getUri()->getPath());
    }

    public function test_available_countries_supports_any_country_flag(): void
    {
        $api = new FakeApi([FakeApi::json([
            'data' => [],
            'meta' => ['total' => 247, 'supports_any_country' => true],
        ])]);

        $result = $api->client->paymentIntents()->availableCountries('TRX2');

        $this->assertTrue($result->supports_any_country);
        $this->assertSame(247, $result->total);
        $this->assertCount(0, $result->countries());
    }

    public function test_available_methods_returns_hydrated_collection(): void
    {
        $api = new FakeApi([FakeApi::json([
            'data' => [
                [
                    'value'       => 'test',
                    'label'       => 'Test method',
                    'description' => 'Sandbox test method',
                    'icon_url'    => 'https://cdn.axazara.com/methods/test.svg',
                    'category'    => 'mobile_money',
                ],
            ],
            'pagination' => ['has_more_pages' => false, 'next_page_url' => null],
        ])]);

        $collection = $api->client->paymentIntents()->availableMethods('TRX1', 'DE');

        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertCount(1, $collection);

        $method = $collection->data[0];
        $this->assertInstanceOf(PaymentMethod::class, $method);
        $this->assertSame('test', $method->value);
        $this->assertSame('Test method', $method->label);
        $this->assertSame('mobile_money', $method->category);

        $request = $api->lastRequest();
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/v1/payment/intents/TRX1/countries/DE/methods', $request->getUri()->getPath());
    }

    public function test_available_methods_forwards_currency_query_param(): void
    {
        $api = new FakeApi([FakeApi::json([
            'data'       => [],
            'pagination' => ['has_more_pages' => false, 'next_page_url' => null],
        ])]);

        $api->client->paymentIntents()->availableMethods('TRX1', 'DE', ['currency' => 'EUR']);

        $request = $api->lastRequest();
        $this->assertSame('/v1/payment/intents/TRX1/countries/DE/methods', $request->getUri()->getPath());
        $this->assertStringContainsString('currency=EUR', $request->getUri()->getQuery());
    }

    public function test_authorize_returns_hydrated_payment_intent(): void
    {
        $api = new FakeApi([FakeApi::json([
            'message' => 'Payment authorised.',
            'data'    => [
                'id'     => 'TRX1',
                'status' => 'processing',
                'amount' => ['value' => 5000, 'currency' => 'USD'],
            ],
        ])]);

        $intent = $api->client->paymentIntents()->authorize('TRX1', ['otp' => '123456']);

        $this->assertInstanceOf(PaymentIntent::class, $intent);
        $this->assertSame('TRX1', $intent->id);
        $this->assertSame('processing', $intent->status);
        $this->assertInstanceOf(Amount::class, $intent->amount);

        $request = $api->lastRequest();
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/v1/payment/intents/TRX1/authorize', $request->getUri()->getPath());
    }

    public function test_authorize_accepts_confirmation_data(): void
    {
        $api = new FakeApi([FakeApi::json([
            'message' => 'Payment authorised.',
            'data'    => ['id' => 'TRX1', 'status' => 'succeeded'],
        ])]);

        $api->client->paymentIntents()->authorize('TRX1', [
            'confirmation_data' => ['token' => 'tok_abc'],
        ]);

        $request = $api->lastRequest();
        $body = json_decode((string) $request->getBody(), true);

        $this->assertIsArray($body);
        $this->assertArrayHasKey('confirmation_data', $body);
        $this->assertSame('tok_abc', $body['confirmation_data']['token']);
    }
}
