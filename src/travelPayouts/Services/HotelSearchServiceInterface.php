<?php

namespace TravelPayouts\Services;

use GuzzleHttp\Exception\GuzzleException;
use TravelPayouts\Enums\EnumSortAsc;
use TravelPayouts\Enums\EnumSortHotel;

interface HotelSearchServiceInterface
{
    /**
     * @param string $locale
     * @param string $currency
     *
     * @return mixed
     * @throws GuzzleException
     */
    public function search(string $locale = 'en_US', string $currency = 'USD');

    public function getMarker(): int;

    public function setMarker(int $marker): self;

    public function getAdultsCount(): int;

    public function setAdultsCount(int $adultsCount): self;

    public function getCheckIn(): string;

    public function setCheckIn(string $checkIn): self;

    public function getCheckOut(): string;

    public function setCheckOut(string $checkOut): self;

    public function getChildrenCount(): int;

    public function setChildrenCount(int $childrenCount): self;

    public function getCustomerIP(): string;

    public function setCustomerIP(string $customerIP): self;

    public function getIata(): string;

    public function setIata(string $iata): self;

    public function getTimeout(): int;

    public function setTimeout(int $timeout): self;

    /** @param array<string, int|string> $options */
    public function getSignature(array $options): string;

    /**
     * Get search results
     *
     * @param string $uuid Search ID
     *
     * @param string $sortBy how to sort:
     *                           popularity - by popularity;
     *                           price - by price;
     *                           name - by name;
     *                           guestScore – by User Rating;
     *                           stars – by number of stars
     * @param int $sortAsc how to sort the values: 1 – ascending; 0 – descending.
     * @param int $roomsCount the maximum number of rooms that are returned in each hotel, from 0 to infinity, where
     *                           0 - no limit
     * @param int $limit maximum number of hotels, from 0 to infinity, where 0 - no limit
     * @param int $offset to skip a number of hotels from 0 to infinity, where 0 - no hotel not passed
     *
     * @return mixed
     * @throws GuzzleException
     */
    public function getSearchResults(
        string $uuid,
        string $sortBy = EnumSortHotel::POPULARITY,
        int $sortAsc = EnumSortAsc::ASCENDING,
        int $roomsCount = 0,
        int $limit = 0,
        int $offset = 0
    );

    public function getHost(): string;

    public function setHost(string $host): self;

    public function getIp(): string;

    public function setIp(string $ip): self;

    public function getCityId(): int;

    public function setCityId(int $cityId): self;

    public function getHotelId(): int;

    public function setHotelId(int $hotelId): self;
}
