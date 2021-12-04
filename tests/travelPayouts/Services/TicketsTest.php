<?php

declare(strict_types=1);

namespace Tests\TravelPayouts\Services;

use DateInterval;
use DateTime;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;
use Tests\TravelPayouts\TokenTrait;
use TravelPayouts\Entity\Airport;
use TravelPayouts\Entity\Ticket;
use TravelPayouts\Services\TicketsService;
use TravelPayouts\Travel;

class TicketsTest extends TestCase
{
    use TokenTrait;

    protected TicketsService $service;

    public function testGetLatestPrices(): void
    {
        $origin = 'LED';
        $destination = 'MOW';

        $tickets = $this->service->getLatestPrices($origin, $destination, false, 'rub', 'year', 1, 10);
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

        $tickets = $this->service->getNearestPlacesMatrix($depart, $return, $origin, $destination);
        $originAirports = $tickets['origins'];
        $destinationAirports = $tickets['destinations'];

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

        $origins = array_map(function (Airport $airport) {
            return $airport->getIata();
        }, $originAirports);
        $destinations = array_map(function (Airport $airport) {
            return $airport->getIata();
        }, $destinationAirports);

        $this->assertContains($origin, $origins);
        $this->assertContains($destination, $destinations);

        /** @var Ticket $ticket */
        foreach ($tickets['prices'] as $ticket) {
            self::assertGreaterThan(0, $ticket->getValue());
            self::assertGreaterThan(0, $ticket->getDistance());

            self::assertContains($ticket->getOrigin()->getIata(), $origins);
            self::assertContains($ticket->getDestination()->getIata(), $destinations);

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
        $origin = 'LED';
        $destination = 'JFK';

        $month = new DateTime('+1 month');

        $depart = $month->setTime(0, 0)->format('Y-m');
        $return = $month->format('Y-m');

        /** @var Ticket $ticket */
        $ticket = $this->service->getDirect($origin, $destination, $depart, $return);

        self::assertEquals(null, $ticket);
    }

    public function testGetMonthly(): void
    {
        $origin = 'MOW';
        $destination = 'HKT';

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

        $directions = $this->service->getAirlineDirections($iata, 10);

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
        $travel = new Travel(self::getToken());

        $this->service = $travel->getTicketsService();

        date_default_timezone_set('UTC');
    }
}
