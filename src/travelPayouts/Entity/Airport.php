<?php

declare(strict_types=1);

namespace TravelPayouts\Entity;

class Airport
{
    private string $iata;

    private string $name;

    /** @var array<string, float> */
    private array $coordinates = [];

    private string $timeZone;

    /** @var array<string, string> */
    private array $nameTranslations = [];

    private ?City $city;

    public function __construct(string $iataCode = '')
    {
        if ($iataCode !== '') {
            $this->setIata($iataCode);
        }
    }

    public function getIata(): string
    {
        return $this->iata;
    }

    public function setIata(string $iata): Airport
    {
        $this->iata = $iata;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Airport
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return array<string, float>
     */
    public function getCoordinates(): array
    {
        return $this->coordinates;
    }

    /** @param array<string, float> $coordinates */
    public function setCoordinates(array $coordinates): Airport
    {
        $this->coordinates = $coordinates;

        return $this;
    }

    public function getTimeZone(): string
    {
        return $this->timeZone;
    }

    public function setTimeZone(string $timeZone): Airport
    {
        $this->timeZone = $timeZone;

        return $this;
    }

    /** @return array<string, string> */
    public function getNameTranslations(): array
    {
        return $this->nameTranslations;
    }

    /**
     * @param array<string, string> $nameTranslations
     * @return Airport
     */
    public function setNameTranslations(array $nameTranslations): Airport
    {
        $this->nameTranslations = $nameTranslations;

        return $this;
    }

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(?City $city): Airport
    {
        $this->city = $city;

        return $this;
    }
}
