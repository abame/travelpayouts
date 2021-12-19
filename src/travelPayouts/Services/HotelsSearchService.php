<?php

declare(strict_types=1);

namespace TravelPayouts\Services;

use Exception;
use TravelPayouts\Components\HotelsClient;
use TravelPayouts\Components\ServiceInterface;
use TravelPayouts\Enums\EnumSortAsc;
use TravelPayouts\Enums\EnumSortHotels;

class HotelsSearchService extends AbstractService implements ServiceInterface, HotelsSearchServiceInterface
{
    private HotelsClient $client;

    private int $marker;

    private string $ip;

    private int $cityId;

    private int $hotelId;

    private string $iata;

    private string $checkIn;

    private string $checkOut;

    private int $adultsCount;

    private int $childrenCount;

    private int $timeout = 20;

    private string $customerIP;

    private string $host;

    public function search(string $locale = 'en_US', string $currency = 'USD')
    {
        $url = 'search/start';

        $options = [
            'marker' => $this->getMarker(),
            'adultsCount' => $this->getAdultsCount(),
            'checkIn' => $this->getCheckIn(),
            'checkOut' => $this->getCheckOut(),
            'childrenCount' => $this->getChildrenCount(),
            'currency' => $currency,
            'customerIP' => $this->getCustomerIP(),
            'iata' => $this->getIata(),
            'lang' => in_array($locale, [
                'en_US',
                'en_GB',
                'de_DE',
                'en_AU',
                'en_CA',
                'en_IE',
                'es_ES',
                'fr_FR',
                'it_IT',
                'pl_PL',
                'th_TH',
            ], true) ? $locale : 'en_US',
            'timeout' => $this->getTimeout(),
            'waitForResults' => '1',
        ];

        $options['signature'] = $this->getSignature($options);

        return $this->client->execute($url, $options, 'GET', false);
    }

    public function getMarker(): int
    {
        return $this->marker;
    }

    public function setMarker(int $marker): self
    {
        $this->marker = $marker;

        return $this;
    }

    public function getAdultsCount(): int
    {
        return $this->adultsCount;
    }

    public function setAdultsCount(int $adultsCount): self
    {
        $this->adultsCount = $adultsCount;

        return $this;
    }

    public function getCheckIn(): string
    {
        return $this->checkIn;
    }

    public function setCheckIn(string $checkIn): self
    {
        $this->checkIn = $checkIn;

        return $this;
    }

    public function getCheckOut(): string
    {
        return $this->checkOut;
    }

    public function setCheckOut(string $checkOut): self
    {
        $this->checkOut = $checkOut;

        return $this;
    }

    public function getChildrenCount(): int
    {
        return $this->childrenCount;
    }

    public function setChildrenCount(int $childrenCount): self
    {
        $this->childrenCount = $childrenCount;

        return $this;
    }

    public function getCustomerIP(): string
    {
        return $this->customerIP;
    }

    public function setCustomerIP(string $customerIP): self
    {
        $this->customerIP = $customerIP;

        return $this;
    }

    public function getIata(): string
    {
        return $this->iata;
    }

    public function setIata(string $iata): self
    {
        $this->iata = $iata;

        return $this;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function setTimeout(int $timeout): self
    {
        $this->timeout = $timeout;

        return $this;
    }

    public function getSignature(array $options): string
    {
        $options['token'] = $this->client->getToken();

        ksort($options);

        return md5(implode(':', $options));
    }

    public function getClient()
    {
        return $this->client;
    }

    public function setClient($client): self
    {
        if (!($client instanceof HotelsClient)) {
            throw new Exception(sprintf('Client of class %s is not allowed here', get_class($client)));
        }

        $this->client = $client;

        return $this;
    }

    public function getSearchResults(
        string $uuid,
        string $sortBy = EnumSortHotels::POPULARITY,
        int $sortAsc = EnumSortAsc::ASCENDING,
        int $roomsCount = 0,
        int $limit = 0,
        int $offset = 0
    ) {
        $url = 'search/getResult';

        $options = [
            'marker' => $this->getMarker(),
            'searchId' => $uuid,
            'limit' => $limit,
            'sortBy' => $sortBy,
            'offset' => $offset,
            'sortAsc' => $sortAsc,
            'roomsCount' => $roomsCount,
        ];

        $options['signature'] = $this->getSignature($options);

        return $this->client->setApiVersion('v1')->execute($url, $options);
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function setHost(string $host): self
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

    public function getCityId(): int
    {
        return $this->cityId;
    }

    public function setCityId(int $cityId): self
    {
        $this->cityId = $cityId;

        return $this;
    }

    public function getHotelId(): int
    {
        return $this->hotelId;
    }

    public function setHotelId(int $hotelId): self
    {
        $this->hotelId = $hotelId;

        return $this;
    }
}
