<?php

declare(strict_types=1);

namespace Tests\TravelPayouts\Services;

use GuzzleHttp\Exception\GuzzleException;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use TravelPayouts\Entity\Airport;
use TravelPayouts\Entity\City;
use TravelPayouts\Entity\Country;
use TravelPayouts\Services\DataService;

class DataTest extends BaseServiceTestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy|DataService */
    protected $service;

    public function testWhereAmI(): void
    {
        $data = $this->service->whereAmI('92.219.161.223', 'en');
        $this->assertEquals('FRA', $data['iata']);
        $this->assertEquals('Frankfurt', $data['name']);
        $this->assertEquals('Germany', $data['country_name']);
        $this->assertEquals('8.570773:50.050735', $data['coordinates']);
    }

    /** @throws GuzzleException */
    public function testGetCurrencies(): void
    {
        $response = $this->service->getCurrencies();

        self::assertArrayHasKey('usd', $response);
        self::assertArrayHasKey('eur', $response);
    }

    public function testGetCity(): void
    {
        $code = 'NYC';
        $name = 'New York';
        $coordinates = ['lat' => 40.71435, 'lon' => -74.005974];
        $timeZone = 'America/New_York';

        $client = $this->getClient('cities', true);
        $this->service->setClient($client->reveal());
        $city = $this->service->getPlace($code);

        $this->assertInstanceOf(City::class, $city);
        $this->assertEquals($code, $city->getIata());
        $this->assertEquals($name, $city->getName());
        $this->assertEquals($coordinates, $city->getCoordinates());
        $this->assertNull($city->getCountry());
        $this->assertEquals($timeZone, $city->getTimeZone());
    }

    public function testGetAirport(): void
    {
        $code = 'JDO';
        $name = 'Regional Do Cariri';
        $coordinates = ['lat' => -7.2, 'lon' => -39.316666];
        $timeZone = 'America/Fortaleza';

        $client = $this->getClient('airports', true);
        $this->service->setClient($client->reveal());
        $airport = $this->service->getAirport($code);

        $this->assertInstanceOf(Airport::class, $airport);
        $this->assertEquals($code, $airport->getIata());
        $this->assertEquals($name, $airport->getName());
        $this->assertEquals($coordinates, $airport->getCoordinates());
        $this->assertNull($airport->getCity()->getCountry());
        $this->assertEquals($timeZone, $airport->getTimeZone());
    }

    public function testGetCountry(): void
    {
        $code = 'US';
        $name = 'United States';
        $currency = 'USD';

        $client = $this->getClient('countries', true);
        $this->service->setClient($client->reveal());
        $country = $this->service->getCountry($code);

        $this->assertInstanceOf(Country::class, $country);
        $this->assertEquals($code, $country->getIata());
        $this->assertEquals($name, $country->getName());
        $this->assertEquals($currency, $country->getCurrency());
    }

    public function testGetAirlines(): void
    {
        $client = $this->getClient('airlines', true);
        $this->service->setClient($client->reveal());
        $json = $this->service->getAirlines();

        foreach ($json as $item) {
            self::assertArrayHasKey('name', $item);
        }
    }

    public function testGetAirlinesAlliances(): void
    {
        $client = $this->getClient('airlines_alliances', true);
        $this->service->setClient($client->reveal());
        $data = $this->service->getAirlinesAlliances();
        $this->assertIsArray($data);
        $this->assertCount(3, $data);
        $this->assertEquals('Star Alliance', $data[0]['name']);
    }

    public function testGetPlanes(): void
    {
        $client = $this->getClient('planes', true);
        $this->service->setClient($client->reveal());
        $json = $this->service->getPlanes();

        $this->assertIsArray($json);

        foreach ($json as $item) {
            self::assertArrayHasKey('name', $item);
            self::assertArrayHasKey('code', $item);
        }
    }

    public function testGetRoutes(): void
    {
        $client = $this->getClient('routes', true);
        $this->service->setClient($client->reveal());
        $json = $this->service->getRoutes();

        $this->assertIsArray($json);

        foreach ($json as $item) {
            self::assertArrayHasKey('airline_iata', $item);
            self::assertArrayHasKey('departure_airport_iata', $item);
        }
    }

    protected function setUp(): void
    {
        $this->service = new DataService();

        date_default_timezone_set('UTC');
    }
}
