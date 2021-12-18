<?php

declare(strict_types=1);

namespace TravelPayouts\Services;

use Exception;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use RuntimeException;
use TravelPayouts\Components\AbstractService;
use TravelPayouts\Components\BaseClient;
use TravelPayouts\Components\ServiceInterface;
use TravelPayouts\Entity\Airport;
use TravelPayouts\Entity\City;
use TravelPayouts\Entity\Country;

class DataService extends AbstractService implements ServiceInterface, DataServiceInterface
{
    /**
     * Include only once data from JSON
     *
     * @var array<string, array<string|int, mixed>>
     */
    public array $data = [
        'countries' => [],
        'cities' => [],
        'airports' => [],
        'airlines' => [],
        'airlines_alliances' => [],
        'planes' => [],
        'routes' => [],
        //hotels
        'amenities' => [],
        'hotel_cities' => [],
    ];

    private BaseClient $client;

    public function whereAmI(string $ip, string $locale = 'en', string $funcName = 'useriata')
    {
        $locale = in_array($locale, ['en', 'ru', 'de', 'fr', 'it', 'pl', 'th'], true) ? $locale : 'en';
        $uri = sprintf('https://www.travelpayouts.com/whereami?locale=%s', $locale);

        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new RuntimeException($ip . ' is not a valid ip');
        }

        $client = $this->getInternalClient();

        $res = $client->request('GET', $uri, [
            'callback' => $funcName,
            'ip' => $ip,
        ]);

        return json_decode($res->getBody()->getContents(), true);
    }

    public function getPlace(string $code)
    {
        $oResult = $this->getCity($code);

        if (!$oResult) {
            $oResult = $this->getAirport($code);
        }

        return $oResult;
    }

    public function getCity(string $code): ?City
    {
        $cities = $this->getCities(true);

        $key = array_search($code, array_column($cities, 'code'), true);

        if ($key === false) {
            return null;
        }

        /** @var  array{code: string, name: string, country_code: string, time_zone: string, name_translations: array<string, string>, coordinates: array<string, float>} $city */
        $city = $cities[$key];

        return $this->createCityObject($city);
    }

    public function getCities(bool $simpleArray = false): array
    {
        /** @var array<int, array<string, string|array<string, string|float>>> $results */
        $results = $this->getClient()->executeJson('/data/en/cities.json');

        return $simpleArray === true ? $results : array_map(function (array $city) {
            /** @var  array{code: string, name: string, country_code: string, time_zone: string, name_translations: array<string, string>, coordinates: array<string, float>} $city */
            return $this->createCityObject($city);
        }, $results);
    }

    public function getClient(): BaseClient
    {
        return $this->client;
    }

    public function setClient($client): self
    {
        if (!($client instanceof BaseClient)) {
            throw new Exception(sprintf('Client of class %s is not allowed here', get_class($client)));
        }

        $this->client = $client;

        $this->getClient()->setApiVersion('data');

        return $this;
    }

    public function getCountryByName(string $name): ?array
    {
        $country = null;
        /** @var array<int, array<string, string|array<string, string>>> $countries */
        $countries = $this->getCountries(true);
        $names = array_column($countries, 'name');
        $index = array_search($name, $names);

        if ($index) {
            $country = $countries[$index];
        }

        return $country;
    }

    public function getCountry(string $code): ?Country
    {
        $jsonArray = $this->getCountries(true);

        $key = array_search($code, array_column($jsonArray, 'code'), true);

        if ($key === false) {
            return null;
        }
        /** @var array{code: string, name: string, currency: string, name_translations: array<string, string>} $country */
        $country = $jsonArray[$key];

        return $this->createCountryObject($country);
    }

    public function getCountries(bool $simpleArray = false): array
    {
        /** @var array<int, array<string, string|array<string, string>>> $results */
        $results = $this->getClient()->executeJson('/data/en/countries.json');

        return $simpleArray === true ? $results : array_map(function (array $country) {
            /** @var array{code: string, name: string, currency: string, name_translations: array<string, string>} $country */
            return $this->createCountryObject($country);
        }, $results);
    }

    public function getAirport(string $code): ?Airport
    {
        $jsonArray = $this->getAirports(true);

        $key = array_search($code, array_column($jsonArray, 'code'), true);

        if ($key === false) {
            return null;
        }

        /** @var array{code: string, name: string, time_zone: string, city_code: string, name_translations: array<string, string>, coordinates: array<string, float>} $airport */
        $airport = $jsonArray[$key];

        return $this->createAirportObject($airport);
    }

    public function getAirports(bool $simpleArray): array
    {
        /** @var array<int, array<string, string|array<string, float|string>>> $results */
        $results = $this->getClient()->executeJson('data/en/airports.json');

        return $simpleArray === true ? $results : array_map(function (array $airport) {
            /** @var array{code: string, name: string, time_zone: string, city_code: string, name_translations: array<string, string>, coordinates: array<string, float>} $airport */
            return $this->createAirportObject($airport);
        }, $results);
    }

    public function getAirlines(): array
    {
        return $this->getClient()->executeJson('/data/en/airlines.json');
    }

    public function getAirlinesAlliances(): array
    {
        return $this->getClient()->executeJson('/data/en/airlines_alliances.json');
    }

    public function getPlanes(): array
    {
        return $this->getClient()->executeJson('/data/en/planes.json');
    }

    public function getRoutes(): array
    {
        return $this->getClient()->executeJson('/data/en/routes.json');
    }

    public function getCurrencies(): array
    {
        $uri = 'https://yasen.aviasales.ru/adaptors/currency.json';

        $client = $this->getInternalClient();

        $response = $client->request('GET', $uri)->getBody();

        /** @var bool|array<string, float> $currencies */
        $currencies = json_decode($response->getContents(), true);

        if (is_bool($currencies)) {
            throw new RuntimeException(sprintf('Unable to decode json response: %s', $response->getContents()));
        }

        return $currencies;
    }

    private function getInternalClient(): ClientInterface
    {
        return new GuzzleClient(
            [
                'headers' =>
                    [
                        'Content-Type' => 'application/json',
                    ],
            ]
        );
    }

    /**
     * @param array{code: string, name: string, country_code: string, time_zone: string, name_translations: array<string, string>, coordinates: array<string, float>} $city
     * @return City
     * @throws GuzzleException
     */
    private function createCityObject(array $city): City
    {
        $country = $this->getCountry($city['country_code']);
        return (new City())
            ->setIata($city['code'])
            ->setName($city['name'])
            ->setNameTranslations($city['name_translations'])
            ->setCoordinates($city['coordinates'])
            ->setTimeZone($city['time_zone'])
            ->setCountry($country);
    }

    /**
     * @param array{code: string, name: string, currency: string, name_translations: array<string, string>} $country
     * @return Country
     */
    private function createCountryObject(array $country): Country
    {
        return (new Country())
            ->setIata($country['code'])
            ->setName($country['name'])
            ->setNameTranslations($country['name_translations'])
            ->setCurrency($country['currency']);
    }

    /**
     * @param array{code: string, name: string, time_zone: string, city_code: string, name_translations: array<string, string>, coordinates: array<string, float>} $airport
     * @return Airport
     * @throws GuzzleException
     */
    private function createAirportObject(array $airport): Airport
    {
        return (new Airport())
            ->setIata($airport['code'])
            ->setName($airport['name'])
            ->setCoordinates($airport['coordinates'])
            ->setNameTranslations($airport['name_translations'])
            ->setTimeZone($airport['time_zone'])
            ->setCity($this->getCity($airport['city_code']));
    }
}
