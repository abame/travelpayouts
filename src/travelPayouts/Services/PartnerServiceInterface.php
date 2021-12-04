<?php

namespace TravelPayouts\Services;

use Exception;
use GuzzleHttp\Exception\GuzzleException;

interface PartnerServiceInterface
{
    /**
     * @return array<string, string>
     * @throws GuzzleException|Exception
     */
    public function getBalance(): array;

    /**
     * @return array<string, mixed>
     * @throws GuzzleException
     */
    public function getPayments(): array;

    /**
     * Returns your number of searches/clicks/reservations and corresponding earnings, grouped by a parameter.
     * Monthly data for hotels and for plane tickets.
     * Data can be filtered by host and/or marker.
     *
     * @param string $groupBy Value to use for grouping. Use date, host, or marker to sort by the respective parameter.
     *                        Should be in ["date", "host", "marker"].
     *                        Default value is date.
     * @param string|null $month First day of month in format "%Y-%m-01". Default value is NOW().
     * @param string|null $host Filter by the host. Default value is null.
     * @param string|null $marker Filter by the marker. Default value is null.
     *
     * @return array<string, mixed>
     * @throws Exception|GuzzleException
     */
    public function getSales(string $groupBy = 'date', string $month = null, string $host = null, string $marker = null): array;

    /**
     * Returns your number of searches/clicks/reservations and corresponding earnings, grouped by date and marker.
     * Monthly data for hotels and for plane tickets.
     * Data can be filtered by host and/or marker.
     *
     * @param string|null $month First day of month in format "%Y-%m-01". Default value is NOW().
     * @param string|null $host Filter by the host. Default value is null.
     * @param string|null $marker Filter by the marker. Default value is null.
     *
     * @return array<string, mixed>
     * @throws Exception|GuzzleException
     */
    public function getDetailedSales(string $month = null, string $host = null, string $marker = null): array;
}
