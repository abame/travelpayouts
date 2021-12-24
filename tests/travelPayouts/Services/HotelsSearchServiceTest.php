<?php

declare(strict_types=1);

namespace Tests\TravelPayouts\Services;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use TravelPayouts\Components\HotelClient;
use TravelPayouts\Services\HotelSearchService;
use TravelPayouts\Services\HotelSearchServiceInterface;

class HotelsSearchServiceTest extends TestCase
{
    use BaseServiceTrait;
    use ProphecyTrait;

    private HotelSearchServiceInterface $service;

    public function testGetSignature(): void
    {
        /** @var ObjectProphecy|HotelClient $client */
        $client = $this->prophesize(HotelClient::class);
        $client->getToken()->shouldBeCalledOnce()->willReturn('DUMMY_TOKEN');
        $client->setApiVersion('v1');
        $this->service->setClient($client->reveal());
        $this->service->setMarker('344747');

        $signature = $this->service->getSignature([
            'checkIn' => '2021-12-24',
            'checkOut' => '2021-12-25',
            'adultsCount' => 2,
            'customerIP' => '94.220.248.74',
            'childrenCount' => 1,
            'childAge1' => 12,
            'lang' => 'en',
            'currency' => 'EUR',
            'waitForResults' => 0,
            'iata' => 'HKT'
        ]);
        $this->assertSame('551c1e09882439deb70fd4ca10febca8', $signature);
    }

    public function testSearch()
    {
        $client = $this->getClient('hotel_search/search_id', false, true, true);
        $client->getToken()->willReturn('DUMMY_TOKEN');
        $this->service->setClient($client->reveal());

        $this->service->setCheckIn('2021-12-10');
        $this->service->setCheckOut('2021-12-13');
        $this->service->setAdultsCount(2);
        $this->service->setCustomerIP('77.111.247.75');
        $this->service->setChildrenCount(1);
        $this->service->setChildAge1(10);
        $this->service->setMarker('344747');
        $this->service->setIata('HKT');

        $data = $this->service->search('ru', 'USD');

        $this->assertCount(2, $data);
        $this->assertArrayHasKey('searchId', $data);
        $this->assertArrayHasKey('status', $data);
        $this->assertSame('ok', $data['status']);
    }

    public function testGetSearchResults()
    {
        $client = $this->getClient('hotel_search/search_result', false, true);
        $this->service->setClient($client->reveal());
        $this->service->setMarker('123');
        $data = $this->service->getSearchResults('863394');
        $this->assertCount(2, $data);
        $this->assertArrayHasKey('result', $data);
        $this->assertCount(10, $data['result']);
        $this->assertArrayHasKey('status', $data);
        $this->assertSame('ok', $data['status']);
    }

    protected function setUp(): void
    {
        $this->service = new HotelSearchService();

        date_default_timezone_set('UTC');
    }
}
