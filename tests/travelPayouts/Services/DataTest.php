<?php

declare(strict_types=1);

namespace Tests\TravelPayouts\Services;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;
use Tests\TravelPayouts\TokenTrait;
use TravelPayouts\Entity\Airport;
use TravelPayouts\Entity\City;
use TravelPayouts\Entity\Country;
use TravelPayouts\Services\DataService;
use TravelPayouts\Travel;

class DataTest extends TestCase
{
    use TokenTrait;

    protected DataService $service;

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
        $code         = 'NYC';
        $name         = 'New York';
        $coordinates  = ['lat' => 40.71435, 'lon' => -74.005974];
        $timeZone     = 'America/New_York';
        $country_code = 'US';

        $city = $this->service->getPlace($code);

        $this->assertInstanceOf(City::class, $city);
        $this->assertEquals($code, $city->getIata());
        $this->assertEquals($name, $city->getName());
        $this->assertEquals($coordinates, $city->getCoordinates());
        $this->assertEquals($country_code, $city->getCountry()->getIata());
        $this->assertEquals($timeZone, $city->getTimeZone());
    }

    public function testGetAirport(): void
    {
        $code         = 'JDO';
        $name         = 'Orlando Bezerra de Menezes Airport';
        $coordinates  = ['lat' => -7.2, 'lon' => -39.316666];
        $timeZone     = 'America/Fortaleza';
        $country_code = 'BR';

        $airport = $this->service->getAirport($code);

        $this->assertInstanceOf(Airport::class, $airport);
        $this->assertEquals($code, $airport->getIata());
        $this->assertEquals($name, $airport->getName());
        $this->assertEquals($coordinates, $airport->getCoordinates());
        $this->assertEquals($country_code, $airport->getCity()->getCountry()->getIata());
        $this->assertEquals($timeZone, $airport->getTimeZone());
    }

    public function testGetCountry(): void
    {
        $code     = 'US';
        $name     = 'United States';
        $currency = 'USD';

        $country = $this->service->getCountry($code);

        $this->assertInstanceOf(Country::class, $country);
        $this->assertEquals($code, $country->getIata());
        $this->assertEquals($name, $country->getName());
        $this->assertEquals($currency, $country->getCurrency());
    }

    public function testGetAirlines(): void
    {
        $json = $this->service->getAirlines();

        foreach ($json as $item) {
            self::assertArrayHasKey('name', $item);
        }
    }

    public function testGetAirlinesAlliances(): void
    {
        $this->expectException(ClientException::class);
        $this->expectExceptionMessageMatches('/Not Found/');
        $this->service->getAirlinesAlliances();
    }

    public function testGetPlanes(): void
    {
        $json = $this->service->getPlanes();

        $this->assertIsArray($json);

        foreach ($json as $item) {
            self::assertArrayHasKey('name', $item);
            self::assertArrayHasKey('code', $item);
        }
    }

    public function testGetRoutes(): void
    {
        $json = $this->service->getRoutes();

        $this->assertIsArray($json);

        foreach ($json as $item) {
            self::assertArrayHasKey('airline_iata', $item);
            self::assertArrayHasKey('departure_airport_iata', $item);
        }
    }

    protected function setUp(): void
    {
        $travel        = new Travel(self::getToken());
        $this->service = $travel->getDataService();

        date_default_timezone_set('UTC');
    }
}
