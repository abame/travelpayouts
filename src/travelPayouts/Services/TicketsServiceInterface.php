<?php

declare(strict_types=1);

namespace TravelPayouts\Services;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use RuntimeException;
use TravelPayouts\Entity\Airport;
use TravelPayouts\Entity\City;
use TravelPayouts\Entity\Ticket;

interface TicketsServiceInterface
{
    public const ECONOMY_CLASS = 0;

    public const BUSINESS_CLASS = 1;

    public const FIRST_CLASS = 2;

    /**
     * Flights found by our users in the last 48 hours.
     *
     * @param string $origin City IATA code.
     * @param string $destination City IATA code.
     * @param bool|false $one_way One-way or round trip. Default value: false
     * @param string $currency Currency of prices. Default value is eur.
     * @param string $period_type Type of date period. Should be in ['year', 'month', 'seasson', 'day']
     * @param int $page Number of the page. Default value: 1
     * @param int $limit Number or the results. Default value: 1
     * @param bool|true $show_to_affiliates false - all prices, true - prices found with affiliate marker
     *                                           (recommended). Default value is true.
     * @param string $sorting Sort by field. Possible values ['price', 'route',
     *                                           'distance_unit_price']
     * @param int $trip_class Class of trip. Can be 0,1,2
     * @param int $trip_duration Trip duration in days
     *
     * @return Ticket[]
     * @throws RuntimeException|Exception|GuzzleException
     */
    public function getLatestPrices(
        string $origin = '',
        string $destination = '',
        bool $one_way = false,
        string $currency = 'eur',
        string $period_type = 'year',
        int $page = 1,
        int $limit = 30,
        bool $show_to_affiliates = true,
        string $sorting = 'price',
        int $trip_class = self::ECONOMY_CLASS,
        int $trip_duration = 0
    ): array;

    /**
     * Prices for each day of the month, grouped by number of stops
     *
     * @param string $origin City IATA code.
     * @param string $destination City IATA code.
     * @param string $month First day of month in format "%Y-%m-01"
     * @param string $currency Currency of prices. Default value is eur.
     * @param bool|true $show_to_affiliates false - all prices, true - prices found with affiliate marker
     *                                       (recommended). Default value is true.
     *
     * @return Ticket[]
     * @throws RuntimeException|Exception|GuzzleException
     */
    public function getMonthMatrix(string $origin, string $destination, string $month, string $currency = 'eur', bool $show_to_affiliates = true): array;

    /**
     * Returns prices for cities closest to the ones specified.
     *
     * @param string $depart_date Depart date in format '%Y-%m-%d'.
     * @param string $return_date Return date in format '%Y-%m-%d'.
     * @param string $origin City IATA code.
     * @param string $destination City IATA code.
     * @param string $currency Currency of prices. Default value is eur.
     * @param bool|true $show_to_affiliates false - all prices, true - prices found with affiliate marker
     *                                       (recommended). Default value is true.
     *
     * @return array{prices: Ticket[], origins: array<array-key, Airport|null>, destinations: array<array-key, Airport|null>}
     * @throws RuntimeException|Exception|GuzzleException
     */
    public function getNearestPlacesMatrix(string $depart_date, string $return_date, string $origin = '', string $destination = '', string $currency = 'eur', bool $show_to_affiliates = true): array;

    /**
     * Price calendar. Returns prices for dates closest to the ones specified.
     *
     * @param string $origin City IATA code.
     * @param string $destination City IATA code.
     * @param string $depart_date Depart date in format '%Y-%m-%d'.
     * @param string $return_date Return date in format '%Y-%m-%d'.
     * @param string $currency Currency of prices. Default value is eur.
     * @param bool|true $show_to_affiliates false - all prices, true - prices found with affiliate marker
     *                                       (recommended). Default value is true.
     *
     * @return Ticket[]
     * @throws RuntimeException|Exception|GuzzleException
     */
    public function getWeekMatrix(string $origin, string $destination, string $depart_date, string $return_date, string $currency = 'eur', bool $show_to_affiliates = true): array;

    /**
     * Returns the cheapest non-stop tickets, as well as tickets with 1 or 2 stops,
     * for the selected route for each day of the selected month.
     *
     * @param string $origin City IATA code.
     * @param string $destination City IATA code.
     * @param string $depart_date Depart date in format '%Y-%m-%d'.
     * @param string $return_date Return date in format '%Y-%m-%d'.
     * @param string $currency Currency of prices. Default value is eur.
     * @param string $calendar_type Field for which the calendar is to be built.
     *                               Default value is departure_date. Should be in ["departure_date", "return_date"].
     * @param int $trip_duration Trip duration in days.
     *
     * @return Ticket[]
     * @throws RuntimeException|Exception|GuzzleException
     */
    public function getCalendar(string $origin, string $destination, string $depart_date, string $return_date = '', string $currency = 'eur', string $calendar_type = 'departure_date', int $trip_duration = 0): array;

    /**
     * Returns the cheapest non-stop tickets, as well as tickets with 1 or 2 stops,
     * for the selected route with filters by departure and return date.
     *
     * @param string $origin City IATA code.
     * @param string $destination City IATA code.
     * @param string $depart_date Depart date in format '%Y-%m-%d'.
     * @param string $return_date Return date in format '%Y-%m-%d'.
     * @param string $currency Currency of prices. Default value is eur.
     *
     * @return Ticket[]
     * @throws RuntimeException|Exception|GuzzleException
     */
    public function getCheap(string $origin, string $destination, string $depart_date = '', string $return_date = '', string $currency = 'eur'): array;

    /**
     * Non-stop tickets. Returns the cheapest non-stop tickets for the selected route with filters by departure and
     * return date.
     *
     * @param string $origin City IATA code.
     * @param string $destination City IATA code.
     * @param string $depart_date Depart date in format '%Y-%m-%d'.
     * @param string $return_date Return date in format '%Y-%m-%d'.
     * @param string $currency Currency of prices. Default value is eur.
     *
     * @return Ticket|null
     * @throws RuntimeException|Exception|GuzzleException
     */
    public function getDirect(string $origin, string $destination, string $depart_date = '', string $return_date = '', string $currency = 'eur'): ?Ticket;

    /**
     * Cheapest tickets grouped by month
     *
     * @param string $origin City IATA code or country code.
     * @param string $destination City IATA code or country code.
     * @param string $currency Currency of prices. Default value is eur.
     *
     * @return Ticket[]
     * @throws RuntimeException|Exception|GuzzleException
     */
    public function getMonthly(string $origin, string $destination, string $currency = 'eur'): array;

    /**
     * Returns most popular routes for selected origin
     *
     * @param string $origin City IATA code.
     *
     * @return Ticket[]
     * @throws RuntimeException|Exception|GuzzleException
     */
    public function getPopularRoutesFromCity(string $origin): array;

    /**
     * Returns the routes that an airline flies and sorts them by popularity.
     *
     * @param string $airline_code Company IATA code in uppercase.
     * @param int $limit Number of records. Default value: 30. Max value: 1000
     *
     * @return array<int, array{origin: City|Airport|null, destination: City|Airport|null, rating: int}>
     * @throws RuntimeException|GuzzleException|Exception
     */
    public function getAirlineDirections(string $airline_code, int $limit = 30): array;
}
