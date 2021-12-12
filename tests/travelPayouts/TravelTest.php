<?php

declare(strict_types=1);

namespace Tests\TravelPayouts;

use PHPUnit\Framework\TestCase;
use TravelPayouts\Services\DataServiceInterface;
use TravelPayouts\Services\FlightServiceInterface;
use TravelPayouts\Services\HotelSearchServiceInterface;
use TravelPayouts\Services\HotelServiceInterface;
use TravelPayouts\Services\PartnerServiceInterface;
use TravelPayouts\Services\TicketsServiceInterface;
use TravelPayouts\Travel;

class TravelTest extends TestCase
{
    protected Travel $travel;

    public function testGetTicketsService(): void
    {
        $ticket = $this->travel->getTicketsService();

        self::assertInstanceOf(TicketsServiceInterface::class, $ticket);
    }

    public function testGetDataService(): void
    {
        $ticket = $this->travel->getDataService();

        self::assertInstanceOf(DataServiceInterface::class, $ticket);
    }

    public function testGetFlightService(): void
    {
        $ticket = $this->travel->getFlightService();

        self::assertInstanceOf(FlightServiceInterface::class, $ticket);
    }

    public function testGetPartnerService(): void
    {
        $ticket = $this->travel->getPartnerService();

        self::assertInstanceOf(PartnerServiceInterface::class, $ticket);
    }

    public function testGetHotelServiceService(): void
    {
        $ticket = $this->travel->getHotelService();

        self::assertInstanceOf(HotelServiceInterface::class, $ticket);
    }

    public function testGetHotelSearchServiceService(): void
    {
        $ticket = $this->travel->getHotelSearchService();

        self::assertInstanceOf(HotelSearchServiceInterface::class, $ticket);
    }

    protected function setUp(): void
    {
        $this->travel = new Travel('DUMMY_TOKEN');
        date_default_timezone_set('UTC');
    }
}
