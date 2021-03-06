<?php

declare(strict_types=1);

namespace Tests\TravelPayouts\Services;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use TravelPayouts\Components\Client;
use TravelPayouts\Enums\PassengerTypes;
use TravelPayouts\Enums\TripClass;
use TravelPayouts\Services\FlightService;
use TravelPayouts\Services\FlightServiceInterface;

class FlightServiceTest extends TestCase
{
    use BaseServiceTrait;
    use ProphecyTrait;

    protected FlightServiceInterface $service;

    public function testSearch(): void
    {
        $client = $this->getClient('flight/flight_search', true, false, true);
        $this->service->setMarker('1234')
        ->setHost('dummy_host')
        ->setIp('1234')
        ->setCurrency('eur');
        $this->service->setClient($client->reveal());
        $data = $this->service->search();
        $this->assertCount(35, $data);
        $this->assertArrayHasKey('metropoly_airports', $data);
        $this->assertCount(2, $data['metropoly_airports']);
        $this->assertArrayHasKey('currency_rates', $data);
        $this->assertCount(175, $data['currency_rates']);
    }

    public function testGetSearchResults(): void
    {
        $client = $this->getClient('flight/flight_search_results', true);
        $this->service->setClient($client->reveal());
        $data = $this->service->getSearchResults('076efe05-eeda-4c30-8e9a-1c36ec565916');
        $this->assertCount(1, $data);
        $this->assertArrayHasKey('proposals', $data[0]);
        $this->assertArrayHasKey('airports', $data[0]);
        $this->assertArrayHasKey('airlines', $data[0]);
        $this->assertArrayHasKey('flight_info', $data[0]);
        $this->assertArrayHasKey('gates_info', $data[0]);
        $this->assertArrayHasKey('search_id', $data[0]);
    }

    public function testGetSignature(): void
    {
        /** @var ObjectProphecy|Client $client */
        $client = $this->prophesize(Client::class);
        $client->getToken()->shouldBeCalledOnce()->willReturn('DUMMY_TOKEN');
        $client->setApiVersion('v1');

        $this->service->setClient($client->reveal());
        $this->service->setMarker('123');
        $signature = $this->service->getSignature([
            'host' => 'beta.aviasales.ru',
            'locale' => 'en',
            'trip_class' => TripClass::FLIGHT_SEARCH_ECONOMY,
            'user_ip' => '94.220.248.74',
            'passengers' => [
                PassengerTypes::ADULTS => 1,
                PassengerTypes::CHILDREN => 0,
                PassengerTypes::INFANTS => 0,
            ],
            'segments' => [
                [
                    'origin' => 'BAX',
                    'destination' => 'MOW',
                    'date' => '2021-12-24',
                ],
                [
                    'origin' => 'MOW',
                    'destination' => 'BAX',
                    'date' => '2021-12-25',
                ]
            ]
        ]);
        $this->assertSame('a6fcf20929a5651391307d1f1bae4f6b', $signature);
    }

    public function testSegments(): void
    {
        $this->service->addSegment('FRA', 'PAR', '2021-12-12');
        $this->assertCount(1, $this->service->getSegments());

        $this->service->clearSegments();
        $this->assertCount(0, $this->service->getSegments());
    }

    public function testPassengers(): void
    {
        $this->service->addPassenger(PassengerTypes::ADULTS);
        $this->service->addPassenger(PassengerTypes::CHILDREN);
        $this->service->addPassenger(PassengerTypes::INFANTS);
        $this->assertCount(3, $this->service->getPassengers());
        $this->assertSame(1, $this->service->getPassengers()[PassengerTypes::ADULTS]);
        $this->assertSame(1, $this->service->getPassengers()[PassengerTypes::CHILDREN]);
        $this->assertSame(1, $this->service->getPassengers()[PassengerTypes::INFANTS]);

        $this->service->removePassenger(PassengerTypes::INFANTS);
        $this->assertCount(3, $this->service->getPassengers());
        $this->assertSame(1, $this->service->getPassengers()[PassengerTypes::ADULTS]);
        $this->assertSame(1, $this->service->getPassengers()[PassengerTypes::CHILDREN]);
        $this->assertSame(0, $this->service->getPassengers()[PassengerTypes::INFANTS]);

        $this->service->clearPassengers();
        $this->assertCount(0, $this->service->getPassengers());
    }

    protected function setUp(): void
    {
        $this->service = new FlightService();

        date_default_timezone_set('UTC');
    }
}
