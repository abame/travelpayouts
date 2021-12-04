<?php

declare(strict_types=1);

namespace TravelPayouts\Services;

use DateTime;
use Exception;
use TravelPayouts\Components\AbstractService;
use TravelPayouts\Components\Client;
use TravelPayouts\Components\ServiceInterface;

class FlightService extends AbstractService implements ServiceInterface, FlightServiceInterface
{
    private Client $client;

    private int $marker;

    private string $host;

    private string $ip;

    private string $currency;

    /** @var array<int, array<string, string>> */
    private array $segments = [];

    /** @var array<string, int> */
    private array $passengers = [
        'adults' => 0,
        'children' => 0,
        'infants' => 0,
    ];

    public function search(string $locale = 'en', string $trip_class = 'Y')
    {
        $url = 'flight_search';

        $options = [
            'json' => [
                'marker' => $this->getMarker(),
                'host' => $this->getHost(),
                'user_ip' => $this->getIp(),
                'locale' => in_array($locale, ['en', 'ru', 'de', 'fr', 'it', 'pl', 'th'], true) ? $locale : 'ru',
                'trip_class' => in_array($trip_class, ['Y', 'C'], true) ? $trip_class : 'Y',
                'passengers' => $this->getPassengers(),
                'segments' => $this->getSegments(),
                'currency' => $this->getCurrency()
            ],
        ];

        $options['json']['signature'] = $this->getSignature($options['json']);

        return $this->client->setApiVersion('v1')->execute($url, $options, 'POST', false);
    }

    public function getMarker(): int
    {
        return $this->marker;
    }

    public function setMarker(int $marker): FlightService
    {
        $this->marker = $marker;

        return $this;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function setHost(string $host): FlightService
    {
        $this->host = $host;

        return $this;
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function setIp(string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }

    public function getPassengers(): array
    {
        return $this->passengers;
    }

    public function getSegments(): array
    {
        return $this->segments;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): FlightService
    {
        $this->currency = $currency;

        return $this;
    }

    public function getSignature(array $options): string
    {
        ksort($options);

        /** @var array<string, int> $passengers */
        $passengers = $options['passengers'];
        ksort($passengers);

        /** @var array<int, array<string, string>> $segments */
        $segments = $options['segments'];

        /** @var array<int, string> $segmentsToImplode */
        $segmentsToImplode = [];
        foreach ($segments as $key => $value) {
            ksort($value);
            $segmentsToImplode[$key] = implode(':', $value);
        }

        /** @var array<string, string> $optionsToImplode */
        $optionsToImplode = $options;
        $optionsToImplode['passengers'] = implode(':', $passengers);
        $optionsToImplode['segments'] = implode(':', $segmentsToImplode);

        $optionsString = implode(':', $optionsToImplode);
        $signatureString = sprintf('%s:%s', $this->client->getToken(), $optionsString);

        return md5($signatureString);
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

        $this->client->setApiVersion('v1');

        return $this;
    }

    public function getSearchResults(string $uuid)
    {
        $url = 'flight_search_results';

        $options = [
            'uuid' => $uuid,
        ];

        return $this->client->setApiVersion('v1')->execute($url, $options);
    }

    public function addSegment(string $origin, string $destination, string $date): FlightService
    {
        $date = new DateTime($date);

        $this->segments[] = [
            'origin' => $origin,
            'destination' => $destination,
            'date' => $date->format('Y-m-d'),
        ];

        return $this;
    }

    public function clearSegments(): FlightService
    {
        $this->segments = [];

        return $this;
    }

    public function addPassenger(string $type, int $count = 1)
    {
        if (isset($this->passengers[$type])) {
            $this->passengers[$type] += $count;

            return $this;
        }

        return false;
    }

    public function removePassenger(string $type, int $count = 1)
    {
        if (isset($this->passengers[$type])) {
            $this->passengers[$type] -= $count;

            return $this;
        }

        return false;
    }

    public function clearPassengers(): FlightService
    {
        $this->passengers = [];

        return $this;
    }
}
