<?php

declare(strict_types=1);

namespace Orqex\Orchestrate\Tests\Unit\Resource;

use Orqex\Orchestrate\Resource\PaymentIntent;
use Orqex\Orchestrate\Tests\Support\FakeApi;
use PHPUnit\Framework\TestCase;

final class CollectionTest extends TestCase
{
    public function test_it_hydrates_a_page_of_typed_resources(): void
    {
        $api = new FakeApi([FakeApi::json([
            'data'       => [['id' => 'TRX1'], ['id' => 'TRX2']],
            'pagination' => ['has_more_pages' => false, 'next_page_url' => null],
        ])]);

        $page = $api->client->paymentIntents()->all();

        $this->assertCount(2, $page);
        $this->assertInstanceOf(PaymentIntent::class, $page->data[0]);
        $this->assertSame('TRX1', $page->data[0]->id);
        $this->assertFalse($page->hasMore());
        $this->assertNull($page->nextPage());
    }

    public function test_auto_paging_iterator_follows_the_cursor(): void
    {
        $api = new FakeApi([
            FakeApi::json([
                'data'       => [['id' => 'TRX1'], ['id' => 'TRX2']],
                'pagination' => [
                    'has_more_pages' => true,
                    'next_page_url'  => 'https://api.orqex.com/v1/payment/intents?cursor=page2',
                ],
            ]),
            FakeApi::json([
                'data'       => [['id' => 'TRX3']],
                'pagination' => ['has_more_pages' => false, 'next_page_url' => null],
            ]),
        ]);

        $ids = [];
        foreach ($api->client->paymentIntents()->all()->autoPagingIterator() as $intent) {
            $ids[] = $intent->id;
        }

        $this->assertSame(['TRX1', 'TRX2', 'TRX3'], $ids);
        $this->assertCount(2, $api->requests());
        $this->assertSame('cursor=page2', $api->requests()[1]->getUri()->getQuery());
    }
}
