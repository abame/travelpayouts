<?php

declare(strict_types=1);

namespace TravelPayouts\Entity;

class HotelLocation
{
    private int $id;

    private string $cityName;

    private string $fullName;

    /** @var array<int|string, mixed> */
    private array $iata;

    private string $countryCode;

    private string $countryName;

    private int $hotelCount;

    /** @var string[] */
    private array $location = [];

    private string $score;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): HotelLocation
    {
        $this->id = $id;

        return $this;
    }

    public function getCityName(): string
    {
        return $this->cityName;
    }

    public function setCityName(string $cityName): HotelLocation
    {
        $this->cityName = $cityName;

        return $this;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): HotelLocation
    {
        $this->fullName = $fullName;

        return $this;
    }

    /** @return array<int|string, mixed> */
    public function getIata(): array
    {
        return $this->iata;
    }

    /** @param array<int|string, mixed> $iata */
    public function setIata(array $iata): HotelLocation
    {
        $this->iata = $iata;

        return $this;
    }

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    public function setCountryCode(string $countryCode): HotelLocation
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    public function getCountryName(): string
    {
        return $this->countryName;
    }

    public function setCountryName(string $countryName): HotelLocation
    {
        $this->countryName = $countryName;

        return $this;
    }

    public function getHotelCount(): int
    {
        return $this->hotelCount;
    }

    public function setHotelCount(int $hotelsCount): HotelLocation
    {
        $this->hotelCount = $hotelsCount;

        return $this;
    }

    /** @return string[] */
    public function getLocation(): array
    {
        return $this->location;
    }

    /** @param string[] $location */
    public function setLocation(array $location): HotelLocation
    {
        $this->location = $location;

        return $this;
    }

    public function getScore(): string
    {
        return $this->score;
    }

    public function setScore(string $score): HotelLocation
    {
        $this->score = $score;

        return $this;
    }
}
