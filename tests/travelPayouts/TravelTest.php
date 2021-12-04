<?php

declare(strict_types=1);

namespace Tests\TravelPayouts;

use PHPUnit\Framework\TestCase;
use TravelPayouts\Services\DataServiceInterface;
use TravelPayouts\Services\FlightServiceInterface;
use TravelPayouts\Services\TicketsServiceInterface;
use TravelPayouts\Travel;

class TravelTest extends TestCase
{
    use TokenTrait;

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

    protected function setUp(): void
    {
        $this->travel = new Travel(self::getToken());
        date_default_timezone_set('UTC');
    }
}
