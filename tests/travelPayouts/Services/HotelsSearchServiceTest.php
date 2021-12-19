<?php

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
            'lang' => 'en_US',
            'currency' => 'EUR',
            'waitForResults' => 0,
            'marker' => 78606,
            'iata' => 'HKT'
        ]);
        $this->assertSame('e7b35db9b73ac49b2e9d56754c80b6da', $signature);
    }

    public function testSearch()
    {
        $this->assertTrue(true);
    }

    public function testGetSearchResults()
    {
        $this->assertTrue(true);
    }

    protected function setUp(): void
    {
        $this->service = new HotelSearchService();

        date_default_timezone_set('UTC');
    }
}
