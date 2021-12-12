<?php

namespace Tests\TravelPayouts\Services;

use DateInterval;
use DateTime;
use Prophecy\PhpUnit\ProphecyTrait;
use TravelPayouts\Entity\HotelLocation;
use TravelPayouts\Entity\HotelSmall;
use TravelPayouts\Services\HotelService;
use TravelPayouts\Travel;

class HotelsServiceTest extends BaseServiceTestCase
{
    use ProphecyTrait;

    private HotelService $service;

    public function testGetHotelTypes(): void
    {
        $client = $this->getClient('hotel_types', false, true);
        $this->service->setClient($client->reveal());
        $data = $this->service->getHotelsTypes();
        $this->assertCount(22, $data);
    }

    public function testGetRoomTypes(): void
    {
        $client = $this->getClient('room_types', false, true);
        $this->service->setClient($client->reveal());
        $data = $this->service->getRoomTypes();
        $this->assertCount(129, $data);
    }

    public function testSearchHotels(): void
    {
        $client = $this->getClient('hotel_search', false, true);
        $this->service->setClient($client->reveal());
        $data = $this->service->searchHotels('moscow', false);
        $this->assertArrayHasKey('hotels', $data);
        $this->assertInstanceOf(HotelSmall::class, $data['hotels'][0]);
        $this->assertArrayHasKey('locations', $data);
        $this->assertInstanceOf(HotelLocation::class, $data['locations'][0]);
    }

    public function testGetHotelsSelection(): void
    {
        $client = $this->getClient('hotel_selection', false, true);
        $this->service->setClient($client->reveal());
        $today = new DateTime('now');
        $tomorrow = clone $today;
        $tomorrow->add(new DateInterval('P1D'));
        $data = $this->service->getHotelsSelection($today->format('Y-m-d'), $tomorrow->format('Y-m-d'), 'popularity', 12209);
        $this->assertArrayHasKey('popularity', $data);
    }

    public function testGetCostOfLiving(): void
    {
        $client = $this->getClient('living_cost', false, true);
        $this->service->setClient($client->reveal());
        $today = new DateTime('now');
        $tomorrow = clone $today;
        $tomorrow->add(new DateInterval('P1D'));
        $data = $this->service->getCostOfLiving('moscow', $today->format('Y-m-d'), $tomorrow->format('Y-m-d'), 'eur', null, 277083);
        $this->assertCount(1, $data);
        $this->assertArrayHasKey('hotelName', $data[0]);
        $this->assertArrayHasKey('location', $data[0]);
    }

    protected function setUp(): void
    {
        $travel = new Travel('DUMMY_TOKEN');

        $this->service = $travel->getHotelService();

        date_default_timezone_set('UTC');
    }
}
