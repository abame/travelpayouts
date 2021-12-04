<?php

namespace Tests\TravelPayouts\Services;

use DateInterval;
use DateTime;
use PHPUnit\Framework\TestCase;
use Tests\TravelPayouts\TokenTrait;
use TravelPayouts\Components\HotelsClient;
use TravelPayouts\Services\HotelsService;
use TravelPayouts\Travel;

class HotelsServiceTest extends TestCase
{
    use TokenTrait;

    private HotelsService $service;

    public function testGetHotelTypes(): void
    {
        $data = $this->service->getHotelsTypes();
        $this->assertCount(14, $data);
    }

    public function testGetRoomTypes(): void
    {
        $data = $this->service->getRoomTypes();
        $this->assertCount(14, $data);
    }

    public function testSearchHotels(): void
    {
        $data = $this->service->searchHotels('moscow', false);
    }

    public function testGetHotelsSelection(): void
    {
        $this->service->setClient(new HotelsClient(self::getToken(), HotelsClient::YASEN_HOST));
        $today = new DateTime('now');
        $tomorrow = clone $today;
        $tomorrow->add(new DateInterval('P1D'));
        $data = $this->service->getHotelsSelection($today->format('Y-m-d'), $tomorrow->format('Y-m-d'), 'popularity', 12209);
    }

    public function testGetCostOfLiving(): void
    {
        $today = new DateTime('now');
        $tomorrow = clone $today;
        $tomorrow->add(new DateInterval('P1D'));
        $data = $this->service->getCostOfLiving('moscow', $today->format('Y-m-d'), $tomorrow->format('Y-m-d'), 'eur', null, 277083);
    }

    protected function setUp(): void
    {
        $travel = new Travel(self::getToken());

        $this->service = $travel->getHotelsService();

        date_default_timezone_set('UTC');
    }
}
