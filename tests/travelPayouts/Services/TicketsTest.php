<?php

declare(strict_types=1);

namespace Tests\TravelPayouts\Services;

use DateInterval;
use DateTime;
use GuzzleHttp\Exception\GuzzleException;
use Prophecy\PhpUnit\ProphecyTrait;
use TravelPayouts\Entity\Ticket;
use TravelPayouts\Services\DataServiceInterface;
use TravelPayouts\Services\TicketsService;
use TravelPayouts\Travel;

class TicketsTest extends BaseServiceTestCase
{
    use ProphecyTrait;

    protected TicketsService $service;
    protected DataServiceInterface $dataService;

    public function testGetLatestPrices(): void
    {
        $origin = 'LED';
        $destination = 'MOW';

        $client = $this->getClient('latest_prices', true);
        $this->service->setClient($client->reveal());
        $tickets = $this->service->getLatestPrices($origin, $destination, false, 'eur', 'year', 1, 10);
        foreach ($tickets as $ticket) {
            self::assertGreaterThan(0, $ticket->getValue());
            self::assertGreaterThan(0, $ticket->getDistance());

            self::assertEquals($origin, $ticket->getOrigin()->getIata());
            self::assertEquals($destination, $ticket->getDestination()->getIata());
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

        $client = $this->getClient('month_matrix', true);
        $this->service->setClient($client->reveal());
        $tickets = $this->service->getMonthMatrix($origin, $destination, $date);
        foreach ($tickets as $ticket) {
            self::assertGreaterThan(0, $ticket->getValue());
            self::assertGreaterThan(0, $ticket->getDistance());

            self::assertEquals($origin, $ticket->getOrigin()->getIata());
            self::assertEquals($destination, $ticket->getDestination()->getIata());

            self::assertGreaterThanOrEqual($dateArray[0], $ticket->getDepartDate()->getTimestamp());
            self::assertLessThanOrEqual($dateArray[1], $ticket->getDepartDate()->getTimestamp());
        }
    }

    /*** @throws GuzzleException */
    public function testGetNearestPlacesMatrix(): void
    {
        $origin = 'LED';
        $destination = 'HKT';
        $depart = (new DateTime('now'))->format('Y-m-d');
        $return = (new DateTime('now'))->add(new DateInterval('P5D'))->format('Y-m-d');

        $client = $this->getClient('nearest_places_matrix', true);
        $this->service->setClient($client->reveal());
        $tickets = $this->service->getNearestPlacesMatrix($depart, $return, $origin, $destination);

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

        /** @var Ticket $ticket */
        foreach ($tickets['prices'] as $ticket) {
            self::assertGreaterThan(0, $ticket->getValue());
            self::assertGreaterThan(0, $ticket->getDistance());

            self::assertGreaterThanOrEqual($departA[0], $ticket->getDepartDate()->getTimestamp());
            self::assertGreaterThanOrEqual($returnA[0], $ticket->getReturnDate()->getTimestamp());

            self::assertLessThanOrEqual($returnA[1], $ticket->getReturnDate()->getTimestamp());
            self::assertLessThanOrEqual($departA[1], $ticket->getDepartDate()->getTimestamp());
        }
    }

    /** @throws GuzzleException */
    public function testGetWeekMatrix(): void
    {
        $origin = 'LED';
        $destination = 'HKT';
        $depart = (new DateTime('now'))->format('Y-m-d');
        $return = (new DateTime('now'))->add(new DateInterval('P28D'))->format('Y-m-d');

        $client = $this->getClient('week_matrix', true);
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
            self::assertEquals($origin, $ticket->getOrigin()->getIata());
            self::assertEquals($destination, $ticket->getDestination()->getIata());
            self::assertGreaterThan(0, $ticket->getValue());
            self::assertGreaterThan(0, $ticket->getDistance());

            self::assertGreaterThanOrEqual($departA[0], $ticket->getDepartDate()->getTimestamp());
            self::assertGreaterThanOrEqual($returnA[0], $ticket->getReturnDate()->getTimestamp());

            self::assertLessThanOrEqual($returnA[1], $ticket->getReturnDate()->getTimestamp());
            self::assertLessThanOrEqual($departA[1], $ticket->getDepartDate()->getTimestamp());
        }
    }

    public function testGetCalendar(): void
    {
        $origin = 'LED';
        $destination = 'HKT';
        $date = (new DateTime('now'))->format('Y-m');

        $client = $this->getClient('calendar', true);
        $this->service->setClient($client->reveal());
        $tickets = $this->service->getCalendar($origin, $destination, $date);

        foreach ($tickets as $ticket) {
            self::assertEquals($origin, $ticket->getOrigin()->getIata());
            self::assertEquals($destination, $ticket->getDestination()->getIata());
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

        $client = $this->getClient('cheap', true);
        $this->service->setClient($client->reveal());
        $tickets = $this->service->getCheap($origin, $destination, $depart, $return);

        /** @var Ticket $ticket */
        foreach ($tickets as $ticket) {
            self::assertEquals($origin, $ticket->getOrigin()->getIata());
            self::assertEquals($destination, $ticket->getDestination()->getIata());
            self::assertGreaterThan(0, $ticket->getValue());

            self::assertGreaterThanOrEqual($departA[0], $ticket->getDepartDate()->getTimestamp());
            self::assertGreaterThanOrEqual($returnA[0], $ticket->getReturnDate()->getTimestamp());

            self::assertLessThanOrEqual($returnA[1], $ticket->getReturnDate()->getTimestamp());
            self::assertLessThanOrEqual($departA[1], $ticket->getDepartDate()->getTimestamp());
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

        $client = $this->getClient('direct', true);
        $this->service->setClient($client->reveal());

        /** @var Ticket $ticket */
        $ticket = $this->service->getDirect($origin, $destination, $depart, $return);

        self::assertEquals($origin, $ticket->getOrigin()->getIata());
        self::assertEquals($destination, $ticket->getDestination()->getIata());
        self::assertGreaterThan(0, $ticket->getValue());

        self::assertGreaterThanOrEqual($departA[0], $ticket->getDepartDate()->getTimestamp());
        self::assertGreaterThanOrEqual($returnA[0], $ticket->getReturnDate()->getTimestamp());

        self::assertLessThanOrEqual($returnA[1], $ticket->getReturnDate()->getTimestamp());
        self::assertLessThanOrEqual($departA[1], $ticket->getDepartDate()->getTimestamp());
    }

    public function testGetDirectNotExist(): void
    {
        $origin = 'MOW';
        $destination = 'LED';

        $month = new DateTime('+1 month');

        $depart = $month->setTime(0, 0)->format('Y-m');
        $return = $month->format('Y-m');

        $ticket = $this->service->getDirect($origin, $destination, $depart, $return);

        $this->assertInstanceOf(Ticket::class, $ticket);
    }

    public function testGetMonthly(): void
    {
        $origin = 'MOW';
        $destination = 'HKT';

        $client = $this->getClient('monthly', true);
        $this->service->setClient($client->reveal());
        $tickets = $this->service->getMonthly($origin, $destination);

        foreach ($tickets as $ticket) {
            self::assertEquals($origin, $ticket->getOrigin()->getIata());
            self::assertEquals($destination, $ticket->getDestination()->getIata());
            self::assertGreaterThan(0, $ticket->getValue());
        }
    }

    public function testGetPopularRoutesFromCity(): void
    {
        $origin = 'LED';

        $tickets = $this->service->getPopularRoutesFromCity($origin);

        /** @var Ticket $ticket */
        foreach ($tickets as $ticket) {
            self::assertEquals($origin, $ticket->getOrigin()->getIata());
            self::assertStringMatchesFormat('%c%c%c', $ticket->getDestination()->getIata());
            self::assertGreaterThan(0, $ticket->getValue());
        }
    }

    public function testGetAirlineDirections(): void
    {
        $iata = 'SU';

        $client = $this->getClient('airline_directions', true);
        $this->service->setClient($client->reveal());
        $directions = $this->service->getAirlineDirections($iata, 10, true);

        foreach ($directions as $dir) {
            $origin = $dir['origin'];
            $destination = $dir['destination'];
            $this->assertNotEmpty($origin->getIata());
            $this->assertNotEmpty($destination->getIata());
            $this->assertGreaterThan(0, $dir['rating']);
        }
    }

    protected function setUp(): void
    {
        $travel = new Travel('DUMMY_TOKEN');

        $this->service = $travel->getTicketsService();
        $this->dataService = $travel->getDataService();

        date_default_timezone_set('UTC');
    }
}
