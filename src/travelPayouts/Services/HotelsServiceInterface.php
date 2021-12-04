<?php

declare(strict_types=1);

namespace TravelPayouts\Services;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use HttpInvalidParamException;
use TravelPayouts\Entity\Hotel;
use TravelPayouts\Entity\HotelLocation;
use TravelPayouts\Entity\HotelLocationSmall;
use TravelPayouts\Entity\HotelSmall;

interface HotelsServiceInterface
{
    public const CITY_TYPE = 'city';

    public const HOTEL_TYPE = 'hotel';

    public const BOTH_TYPE = 'both';

    /**
     * Hotel search by name or location
     * Search for a specific location or name places of a hotel (city, island).
     *
     * @param string $query the main parameter, it is set: as a text; by the longitude and latitude
     *                                    (also displays the nearest objects).
     * @param bool $searchByCoordinates if query parameter have coordinates
     * @param string $lookFor objects displayed in the results (CITY_TYPE, HOTEL_TYPE, BOTH)
     * @param string $lang the display language. It can take any iso-language code
     * @param int $limit limitation of output results from 1 to 100, default - 10.
     * @param bool $convertCase automatically change the keyboard layout
     *
     * @return array{hotels?: array<int, HotelSmall>, locations?: array<int,HotelLocation|HotelLocationSmall>}
     * @throws GuzzleException
     */
    public function searchHotels(
        string $query,
        bool $searchByCoordinates,
        string $lookFor = self::BOTH_TYPE,
        string $lang = 'en',
        int $limit = 30,
        bool $convertCase = true
    ): array;

    /**
     * Displays the cost of living in hotels
     *
     * Request is used to get the price of hotel's rooms from the cache for the period.
     * It doesn't contain information about rooms availability.
     *
     * @param string $location
     * @param string $checkIn
     * @param string $checkOut
     * @param string $currency
     * @param int|null $locationId
     * @param int|null $hotelId
     * @param string|null $hotel
     * @param int $adults
     * @param int $children
     * @param int $infants
     * @param int $limit
     * @param string|null $customerIp
     *
     * @return array<int, array<string, int|string|HotelLocationSmall|int[]>>
     * @throws GuzzleException|Exception
     */
    public function getCostOfLiving(
        string $location,
        string $checkIn,
        string $checkOut,
        string $currency = 'eur',
        int $locationId = null,
        int $hotelId = null,
        string $hotel = null,
        int $adults = 2,
        int $children = 0,
        int $infants = 0,
        int $limit = 4,
        string $customerIp = null
    ): array;

    /**
     * Hotels Selections
     *
     * The request recovers the list of the specific hotels according to the ID of a location.
     * A collection is formed according to the specified period. If the period is not specified,
     * a collection shall be formed from the hotels, found for the last three days.
     *
     * @param string $checkIn the date of check-in
     * @param string $checkOut the date of check-out
     * @param string $type type of hotels from request /tp/public/available_selections.json
     * @param string $currency currency of response, default usd
     * @param string $language language of response (pt, en, fr, de, id, it, pl, es, th, ru)
     * @param int $id id of the city
     * @param int $limit limitation of output results from 1 to 100, default - 10
     *
     * @return array{popularity: array<int, array<string, int|string>>}
     * @throws GuzzleException|HttpInvalidParamException|Exception
     */
    public function getHotelsSelection(
        string $checkIn,
        string $checkOut,
        string $type,
        int $id,
        string $currency = 'usd',
        string $language = 'en',
        int $limit = 10
    ): array;

    /**
     * The types of hotel collections
     *
     * The request recovers the list of all available separate collections.
     * This type is used for searching of the hotels with a discount.
     *
     * @param int $id hotel id
     *
     * @return string[]
     * @throws GuzzleException|Exception
     */
    public function getHotelCollectionsTypes(int $id): array;

    /**
     * @param int $id location id
     *
     * @return Hotel[]
     * @throws GuzzleException|Exception
     */
    public function getHotelsListByLocation(int $id): array;

    /**
     * @param string $language
     *
     * @return array<int, string>
     * @throws GuzzleException|Exception
     */
    public function getRoomTypes(string $language = 'en'): array;

    /**
     * @param string $language
     * @return array<int, string>
     * @throws GuzzleException|Exception
     */
    public function getHotelsTypes(string $language = 'en'): array;

    public function getHotelPhoto(int $hotelId, int $photoId, string $photoSize, bool $auto = false): string;
}
