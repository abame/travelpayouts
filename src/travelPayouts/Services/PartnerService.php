<?php

declare(strict_types=1);

namespace TravelPayouts\Services;

use DateTime;
use Exception;
use TravelPayouts\Components\Client;
use TravelPayouts\Components\ServiceInterface;

class PartnerService extends AbstractService implements ServiceInterface, PartnerServiceInterface
{
    private Client $client;

    public function getBalance(): array
    {
        $url = 'statistics/balance';

        /** @var array{success: bool, data: array<string, string>} $response */
        $response = $this->client->execute($url, []);

        return $response['data'];
    }

    public function getPayments(): array
    {
        $url = 'statistics/payments';

        /** @var array{success: bool, data: array{payments: array<string, mixed>}} $response */
        $response = $this->client->execute($url, []);

        return $response['data']['payments'];
    }

    public function getSales(string $groupBy = 'date', string $month = null, string $host = null, string $marker = null): array
    {
        $url = 'statistics/sales';

        $date = new DateTime($month === null ? 'now' : $month);

        $options = [
            'group_by' => in_array($groupBy, ['date', 'host', 'marker'], true) ? $groupBy : null,
            'month' => $date->modify('first day of this month')->setTime(0, 00)->format('Y-m-d'),
            'host_filter' => $host,
            'marker_filter' => $marker,
        ];

        /** @var array{success: bool, data: array{sales: array<string, mixed>}} $response */
        $response = $this->getClient()->execute($url, $options);

        return $response['data']['sales'];
    }

    public function getDetailedSales(string $month = null, string $host = null, string $marker = null): array
    {
        $url = 'statistics/detailed-sales';

        $date = new DateTime($month === null ? 'now' : $month);

        $options = [
            'group_by' => 'date_marker',
            'month' => $date->modify('first day of this month')->setTime(0, 0)->format('Y-m-d'),
            'host_filter' => $host,
            'marker_filter' => $marker,
        ];

        /** @var array{success: bool, data: array{sales: array<string, mixed>}} $response */
        $response = $this->getClient()->execute($url, $options);

        return $response['data']['sales'];
    }

    public function getClient()
    {
        return $this->client;
    }

    public function setClient($client): self
    {
        if (!($client instanceof Client)) {
            throw new Exception(sprintf('Client of class %s is not allowed here', get_class($client)));
        }

        $this->client = $client;

        return $this;
    }
}
