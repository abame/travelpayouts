<?php

declare(strict_types=1);

namespace TravelPayouts\Services;

use Exception;
use TravelPayouts\Components\HotelClient;
use TravelPayouts\Components\ServiceInterface;
use TravelPayouts\Enums\EnumSortAsc;
use TravelPayouts\Enums\EnumSortHotel;

class HotelSearchService extends AbstractService implements ServiceInterface, HotelSearchServiceInterface
{
    private HotelClient $client;

    private string $marker;

    private string $ip;

    private int $cityId;

    private int $hotelId;

    private string $iata;

    private string $checkIn;

    private string $checkOut;

    private int $adultsCount;

    private int $childrenCount;

    private int $childAge1 = 1;

    private int $childAge2 = 1;

    private int $childAge3 = 1;

    private int $timeout = 20;

    private string $customerIP;

    private string $host;

    public function search(string $locale = 'en_US', string $currency = 'EUR')
    {
        $url = 'search/start';
        $locale = in_array($locale, ['en', 'ru', 'de', 'fr', 'it', 'pl', 'th'], true) ? $locale : 'en';

        $options = [
            'marker' => $this->getMarker(),
            'adultsCount' => $this->getAdultsCount(),
            'checkIn' => $this->getCheckIn(),
            'checkOut' => $this->getCheckOut(),
            'childrenCount' => $this->getChildrenCount(),
            'childAge1' => $this->getChildrenCount() > 0 ? $this->getChildAge1() : 0,
            'childAge2' => $this->getChildrenCount() > 1 ? $this->getChildAge2() : 0,
            'childAge3' => $this->getChildrenCount() > 2 ? $this->getChildAge3() : 0,
            'currency' => $currency,
            'customerIP' => $this->getCustomerIP(),
            'iata' => $this->getIata(),
            'lang' => $locale,
            'timeout' => $this->getTimeout(),
            'waitForResults' => '1',
        ];

        $options['signature'] = $this->getSignature($options);

        return $this->client->execute($url, $options, 'GET', false);
    }

    public function getSearchResults(
        string $uuid,
        string $sortBy = EnumSortHotel::POPULARITY,
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

    public function getSignature(array $options): string
    {
        unset($options['marker']);
        ksort($options);
        $signatureString = sprintf('%s:%s', $this->client->getToken(), $this->getMarker());
        $signatureString = sprintf('%s:%s', $signatureString, implode(':', $options));

        return md5($signatureString);
    }

    public function getMarker(): string
    {
        return $this->marker;
    }

    public function setMarker(string $marker): self
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

    public function getChildAge1(): int
    {
        return $this->childAge1;
    }

    public function setChildAge1(int $childAge1): void
    {
        $this->childAge1 = $childAge1;
    }

    public function getChildAge2(): int
    {
        return $this->childAge2;
    }

    public function setChildAge2(int $childAge2): void
    {
        $this->childAge2 = $childAge2;
    }

    public function getChildAge3(): int
    {
        return $this->childAge3;
    }

    public function setChildAge3(int $childAge3): void
    {
        $this->childAge3 = $childAge3;
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

    public function getClient()
    {
        return $this->client;
    }

    public function setClient($client): self
    {
        if (!($client instanceof HotelClient)) {
            throw new Exception(sprintf('Client of class %s is not allowed here', get_class($client)));
        }

        $this->client = $client;

        return $this;
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
