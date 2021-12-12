<?php

namespace TravelPayouts\Services;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use RuntimeException;
use TravelPayouts\Entity\Airport;
use TravelPayouts\Entity\City;
use TravelPayouts\Entity\Country;

interface DataServiceInterface
{
    /**
     * Detect city and nearest airport of the user with given ip
     * @return mixed
     * @throws GuzzleException
     */
    public function whereAmI(string $ip, string $locale = 'ru', string $funcName = 'useriata');

    /**
     * Get City or Airport
     *
     * @param string $code
     *
     * @return null|Airport|City
     * @throws GuzzleException
     */
    public function getPlace(string $code);

    /**
     * @throws GuzzleException
     */
    public function getCity(string $code): ?City;

    /**
     * Get cities
     *
     * @param bool $simpleArray
     *
     * @return City[]|array<int, array<string, string|array<string, string|float>>>
     * @throws GuzzleException|Exception
     */
    public function getCities(bool $simpleArray = false): array;

    /** @throws GuzzleException */
    public function getCountry(string $code): ?Country;

    /**
     * @return Country[]|array<int, array<string, string|array<string, string>>>
     * @throws GuzzleException|Exception
     */
    public function getCountries(bool $simpleArray = false): array;

    /**
     * @param string $code
     * @return null|Airport
     * @throws GuzzleException
     */
    public function getAirport(string $code): ?Airport;

    /**
     * Get airports
     *
     * @param bool $simpleArray
     *
     * @return Airport[]|array<int, array<string, string|array<string, float|string>>>
     * @throws GuzzleException|Exception
     */
    public function getAirports(bool $simpleArray): array;

    /**
     * @return array<string, mixed>
     * @throws GuzzleException|Exception
     */
    public function getAirlines(): array;

    /**
     * Get airlines alliances
     *
     * @return array<string, mixed>
     * @throws GuzzleException|Exception
     */
    public function getAirlinesAlliances(): array;

    /**
     * Get planes codes
     *
     * @return array<string, mixed>
     * @throws GuzzleException|Exception
     */
    public function getPlanes(): array;

    /**
     * @return array<string, mixed>
     * @throws GuzzleException|Exception
     */
    public function getRoutes(): array;

    /**
     * @return null|array<string, string|array<string, string>>
     * @throws GuzzleException
     */
    public function getCountryByName(string $name): ?array;

    /**
     * @return array<int, array<string, string>>
     * @throws RuntimeException
     */
    public function getHotelAmenities(): array;

    /**
     * @return array<int, array<string, string|null|array<int, array<string, array<int, array<string, string>>>>>>
     * @throws RuntimeException
     */
    public function getHotelCities(): array;

    /**
     * @return array<int, array<string, string>>
     * @throws RuntimeException
     */
    public function getHotelCountries(): array;

    /**
     * @return array<string, float>
     * @throws GuzzleException
     */
    public function getCurrencies(): array;
}
