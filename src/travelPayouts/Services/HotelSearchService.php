<?php

declare(strict_types=1);

namespace TravelPayouts\Services;

use Exception;
use RuntimeException;
use TravelPayouts\Components\HotelClient;
use TravelPayouts\Components\ServiceInterface;
use TravelPayouts\Enums\EnumSortAsc;
use TravelPayouts\Enums\EnumSortHotel;

class HotelSearchService extends AbstractService implements ServiceInterface, HotelSearchServiceInterface
{
    private HotelClient $client;

    private string $marker;

    private string $ip;

    private ?int $cityId = null;

    private ?int $hotelId = null;

    private ?string $iata = null;

    private string $checkIn;

    private string $checkOut;

    private int $adultsCount;

    private int $childrenCount;

    private int $childAge1 = 1;

    private int $childAge2 = 1;

    private int $childAge3 = 1;

    private int $timeout = 20;

    private string $customerIP;

    private ?string $host;

    public function search(string $locale = 'en', string $currency = 'EUR', int $waitForResults = 0)
    {
        $url = 'search/start';
        $locale = in_array($locale, ['en', 'ru', 'de', 'fr', 'it', 'pl', 'th'], true) ? $locale : 'en';
        if ($this->getIata() === null && $this->getCityId() === null && $this->getHotelId() === null) {
            throw new RuntimeException('cityId, hotelId or iata should not be null');
        }
        if ($this->getChildrenCount() > 3) {
            throw new RuntimeException('no more then 3 children allowed');
        }

        $options = [
            'marker' => $this->getMarker(),
            'adultsCount' => $this->getAdultsCount(),
            'checkIn' => $this->getCheckIn(),
            'checkOut' => $this->getCheckOut(),
            'childrenCount' => $this->getChildrenCount(),
            'currency' => $currency,
            'customerIP' => $this->getCustomerIP(),
            'lang' => $locale,
            'timeout' => $this->getTimeout(),
            'waitForResults' => $waitForResults,
        ];
        if ($this->getIata() !== null) {
            $options['iata'] = $this->getIata();
        }
        if ($this->getCityId() !== null) {
            $options['cityId'] = $this->getCityId();
        }
        if ($this->getHotelId() !== null) {
            $options['hotelId'] = $this->getHotelId();
        }
        if ($this->getChildrenCount() >= 1) {
            $options['childAge1'] = $this->getChildAge1();
        }
        if ($this->getChildrenCount() >= 2) {
            $options['childAge2'] = $this->getChildAge2();
        }
        if ($this->getChildrenCount() === 3) {
            $options['childAge3'] = $this->getChildAge3();
        }

        $options['signature'] = $this->getSignature($options);

        return $this->client->execute($url, $options, 'GET', true);
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

        $this->client->setApiVersion('v1');
        return $this->client->execute($url, $options);
    }

    public function getSignature(array $options): string
    {
        unset($options['marker']);
        unset($options['timeout']);
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

    public function getIata(): ?string
    {
        return $this->iata;
    }

    public function setIata(?string $iata): self
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

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function setHost(?string $host): self
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

    public function getCityId(): ?int
    {
        return $this->cityId;
    }

    public function setCityId(?int $cityId): self
    {
        $this->cityId = $cityId;

        return $this;
    }

    public function getHotelId(): ?int
    {
        return $this->hotelId;
    }

    public function setHotelId(?int $hotelId): self
    {
        $this->hotelId = $hotelId;

        return $this;
    }
}
