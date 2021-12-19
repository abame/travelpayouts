<?php

namespace TravelPayouts\Services;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use TravelPayouts\Enums\TripClass;

interface FlightServiceInterface
{
    /**
     * @param string $locale
     * @param string $trip_class
     *
     * @return mixed
     * @throws GuzzleException|Exception
     */
    public function search(string $locale = 'en', string $trip_class = TripClass::FLIGHT_SEARCH_ECONOMY);

    /**
     * Get search results
     *
     * @param string $uuid Search ID
     *
     * @return mixed
     * @throws GuzzleException
     */
    public function getSearchResults(string $uuid);

    public function getMarker(): string;

    public function setMarker(string $marker): self;

    public function getHost(): string;

    public function setHost(string $host): self;

    public function getIp(): string;

    public function setIp(string $ip): self;

    /** @return array<string, int> */
    public function getPassengers(): array;

    /** @return array<int, array<string, string>> */
    public function getSegments(): array;

    public function getCurrency(): string;

    public function setCurrency(string $currency): self;

    /**
     * @param array<string, array<int|string, array<string, string>|int>|int|string> $options
     * @throws Exception
     */
    public function getSignature(array $options): string;

    /**
     * Add segment
     *
     * @param string $origin
     * @param string $destination
     * @param string $date
     *
     * @return $this
     * @throws Exception
     */
    public function addSegment(string $origin, string $destination, string $date): self;

    public function clearSegments(): self;

    /**
     * Add $count passenger of $type type
     *
     * @param string $type
     * @param int $count
     *
     * @return $this|bool
     */
    public function addPassenger(string $type, int $count = 1);

    /**
     * Remove $count passengers of $type type
     *
     * @param string $type
     * @param int $count
     *
     * @return $this|bool
     */
    public function removePassenger(string $type, int $count = 1);

    /**
     * Remove all passengers
     *
     * @return $this
     */
    public function clearPassengers(): self;
}
