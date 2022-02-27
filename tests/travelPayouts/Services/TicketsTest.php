<?php

declare(strict_types=1);

namespace Tests\TravelPayouts\Services;

use DateInterval;
use DateTime;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use TravelPayouts\Entity\Airport;
use TravelPayouts\Entity\City;
use TravelPayouts\Entity\Ticket;
use TravelPayouts\Services\DataServiceInterface;
use TravelPayouts\Services\TicketsService;
use TravelPayouts\Travel;

class TicketsTest extends TestCase
{
    use ProphecyTrait;
    use BaseServiceTrait;

    protected TicketsService $service;

    public function testGetLatestPrices(): void
    {
        $origin = 'LED';
        $destination = 'MOW';

        $client = $this->getClient('tickets/latest_prices', true);
        $this->service->setClient($client->reveal());
        $tickets = $this->service->getLatestPrices($origin, $destination, false, 'eur', 'year', 1, 10);
        foreach ($tickets as $ticket) {
            self::assertGreaterThan(0, $ticket->getValue());
            self::assertGreaterThan(0, $ticket->getDistance());

            self::assertInstanceOf(City::class, $ticket->getOrigin());
            self::assertInstanceOf(City::class, $ticket->getDestination());
        }
    }

    public function testGetMonthMatrix(): void
    {
        $origin = 'LED';
        $destination = 'HKT';
        $month = new DateTime('+1 month');
        $date = $month->modify('first day of this month')->format('Y-m-d H:i:s');

        $dateArray = [
            $month->setTime(0, 0)->getTimestamp(),
            $month->modify('last day of this month')->getTimestamp(),
        ];

        $client = $this->getClient('tickets/month_matrix', true);
        $this->service->setClient($client->reveal());
        $tickets = $this->service->getMonthMatrix($origin, $destination, $date);
        foreach ($tickets as $ticket) {
            self::assertGreaterThan(0, $ticket->getValue());
            self::assertGreaterThan(0, $ticket->getDistance());

            self::assertInstanceOf(City::class, $ticket->getOrigin());
            self::assertInstanceOf(City::class, $ticket->getDestination());

            self::assertTrue($month->setTime(0, 0)->getTimestamp() >= $ticket->getDepartDate()->getTimestamp());
            self::assertFalse($month->modify('last day of this month')->getTimestamp() <= $ticket->getDepartDate()->getTimestamp());
        }
    }

    /*** @throws GuzzleException */
    public function testGetNearestPlacesMatrix(): void
    {
        $origin = 'LED';
        $destination = 'HKT';
        $depart = (new DateTime('now'))->setTime(0, 0);
        $return = (new DateTime('now'))->add(new DateInterval('P5D'))->setTime(0, 0);

        $client = $this->getClient('tickets/nearest_places_matrix', true);
        $this->service->setClient($client->reveal());
        $tickets = $this->service->getNearestPlacesMatrix($depart->format('Y-m-d'), $return->format('Y-m-d'), $origin, $destination);

        /** @var Ticket $ticket */
        foreach ($tickets['prices'] as $ticket) {
            self::assertGreaterThan(0, $ticket->getValue());
            self::assertGreaterThan(0, $ticket->getDistance());
        }
    }

    /** @throws GuzzleException */
    public function testGetWeekMatrix(): void
    {
        $origin = 'LED';
        $destination = 'HKT';
        $depart = (new DateTime('now'))->format('Y-m-d');
        $return = (new DateTime('now'))->add(new DateInterval('P28D'))->format('Y-m-d');

        $client = $this->getClient('tickets/week_matrix', true);
        $this->service->setClient($client->reveal());
        $tickets = $this->service->getWeekMatrix($origin, $destination, $depart, $return);

        $departObject = new DateTime($depart);
        $returnObject = new DateTime($return);

        $departA = [
            $departObject->modify('-7 day')->getTimestamp(),
            $departObject->modify('+14 day')->getTimestamp(),
        ];

        $returnA = [
            $returnObject->modify('-7 day')->getTimestamp(),
            $returnObject->modify('+14 day')->getTimestamp(),
        ];

        foreach ($tickets as $ticket) {
            self::assertInstanceOf(City::class, $ticket->getOrigin());
            self::assertInstanceOf(City::class, $ticket->getDestination());
            self::assertGreaterThan(0, $ticket->getValue());
            self::assertGreaterThan(0, $ticket->getDistance());

            self::assertTrue($departA[0] >= $ticket->getDepartDate()->getTimestamp());
            self::assertTrue($returnA[0] >= $ticket->getDepartDate()->getTimestamp());

            self::assertFalse($returnA[1] <= $ticket->getDepartDate()->getTimestamp());
            self::assertFalse($departA[1] <= $ticket->getDepartDate()->getTimestamp());
        }
    }

    public function testGetCalendar(): void
    {
        $origin = 'LED';
        $destination = 'HKT';
        $date = (new DateTime('now'))->format('Y-m');

        $client = $this->getClient('tickets/calendar', true);
        $this->service->setClient($client->reveal());
        $tickets = $this->service->getCalendar($origin, $destination, $date);

        foreach ($tickets as $ticket) {
            self::assertInstanceOf(City::class, $ticket->getOrigin());
            self::assertInstanceOf(City::class, $ticket->getDestination());
            self::assertGreaterThan(0, $ticket->getValue());
        }
    }

    public function testGetCheap(): void
    {
        $origin = 'MOW';
        $destination = 'HKT';

        $month = new DateTime('+1 month');

        $depart = $month->setTime(0, 0)->format('Y-m');

        $departA = [
            $month->modify('last day of previous month')->getTimestamp(),
            $month->modify('+1 month')->modify('first day of next month')->getTimestamp(),
        ];

        $return = $month->format('Y-m');

        $returnA = [
            $month->modify('last day of previous month')->getTimestamp(),
            $month->modify('last day of next month')->getTimestamp(),
        ];

        $client = $this->getClient('tickets/cheap', true);
        $this->service->setClient($client->reveal());
        $tickets = $this->service->getCheap($origin, $destination, $depart, $return);

        /** @var Ticket $ticket */
        foreach ($tickets as $ticket) {
            self::assertInstanceOf(City::class, $ticket->getOrigin());
            self::assertInstanceOf(City::class, $ticket->getDestination());
            self::assertGreaterThan(0, $ticket->getValue());

            self::assertTrue($departA[0] >= $ticket->getDepartDate()->getTimestamp());
            self::assertTrue($returnA[0] >= $ticket->getDepartDate()->getTimestamp());

            self::assertFalse($returnA[1] <= $ticket->getDepartDate()->getTimestamp());
            self::assertFalse($departA[1] <= $ticket->getDepartDate()->getTimestamp());
        }
    }

    public function testGetDirect(): void
    {
        $origin = 'MOW';
        $destination = 'JFK';

        $month = new DateTime('+1 month');

        $depart = $month->setTime(0, 0)->format('Y-m');

        $departA = [
            $month->modify('last day of previous month')->getTimestamp(),
            $month->modify('+1 month')->modify('first day of next month')->getTimestamp(),
        ];

        $return = $month->format('Y-m');

        $returnA = [
            $month->modify('last day of previous month')->getTimestamp(),
            $month->modify('last day of next month')->getTimestamp(),
        ];

        $client = $this->getClient('tickets/direct', true);
        $this->service->setClient($client->reveal());

        /** @var Ticket $ticket */
        $ticket = $this->service->getDirect($origin, $destination, $depart, $return);

        self::assertInstanceOf(City::class, $ticket->getOrigin());
        self::assertInstanceOf(City::class, $ticket->getDestination());
        self::assertGreaterThan(0, $ticket->getValue());

        self::assertTrue($departA[0] >= $ticket->getDepartDate()->getTimestamp());
        self::assertTrue($returnA[0] >= $ticket->getDepartDate()->getTimestamp());

        self::assertFalse($returnA[1] <= $ticket->getDepartDate()->getTimestamp());
        self::assertFalse($departA[1] <= $ticket->getDepartDate()->getTimestamp());
    }

    public function testGetMonthly(): void
    {
        $origin = 'MOW';
        $destination = 'HKT';

        $client = $this->getClient('tickets/monthly', true);
        $this->service->setClient($client->reveal());
        $tickets = $this->service->getMonthly($origin, $destination);

        foreach ($tickets as $ticket) {
            self::assertInstanceOf(City::class, $ticket->getOrigin());
            self::assertInstanceOf(City::class, $ticket->getDestination());
            self::assertGreaterThan(0, $ticket->getValue());
        }
    }

    public function testGetPopularRoutesFromCity(): void
    {
        $origin = 'LED';

        $client = $this->getClient('tickets/city_direction', true);
        $this->service->setClient($client->reveal());
        $tickets = $this->service->getPopularRoutesFromCity($origin);

        /** @var Ticket $ticket */
        foreach ($tickets as $ticket) {
            self::assertInstanceOf(City::class, $ticket->getOrigin());
            self::assertInstanceOf(City::class, $ticket->getDestination());
            self::assertGreaterThan(0, $ticket->getValue());
        }
    }

    public function testGetAirlineDirections(): void
    {
        $iata = 'SU';

        $client = $this->getClient('tickets/airline_directions', true);
        $this->service->setClient($client->reveal());
        $directions = $this->service->getAirlineDirections($iata, 10, true);

        foreach ($directions as $dir) {
            $this->assertInstanceOf(City::class, $dir['origin']);
            $this->assertInstanceOf(City::class, $dir['destination']);
            $this->assertGreaterThan(0, $dir['rating']);
        }
    }

    protected function setUp(): void
    {
        $travel = new Travel('DUMMY_TOKEN');

        /** @var ObjectProphecy|DataServiceInterface $dataService */
        $dataService = $this->prophesize(DataServiceInterface::class);
        $dataService->getPlace(Argument::type('string'))->willReturn(new City());
        $dataService->getAirport(Argument::type('string'))->willReturn(new Airport());
        $dataService->getAirports(true)->willReturn([new Airport()]);
        $this->service = $travel->getTicketsService();
        $this->service->setDataService($dataService->reveal());

        date_default_timezone_set('UTC');
    }
}
