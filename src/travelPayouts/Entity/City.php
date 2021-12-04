<?php

declare(strict_types=1);

namespace TravelPayouts\Entity;

class City
{
    /**
     * @var string
     */
    private string $iata;

    /**
     * @var string
     */
    private string $name;

    /** @var array<string, float> */
    private array $coordinates;

    /**
     * @var string
     */
    private string $timeZone;

    /** @var array<string, string> */
    private array $nameTranslations;

    private ?Country $country;

    public function __construct(string $code = '')
    {
        if ($code !== '') {
            $this->setIata($code);
        }
    }

    public function getIata(): string
    {
        return $this->iata;
    }

    public function setIata(string $iata): City
    {
        $this->iata = $iata;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): City
    {
        $this->name = $name;

        return $this;
    }

    /** @return array<string, float> */
    public function getCoordinates(): array
    {
        return $this->coordinates;
    }

    /**
     * @param array<string, float> $coordinates
     *
     * @return City
     */
    public function setCoordinates(array $coordinates): City
    {
        $this->coordinates = $coordinates;

        return $this;
    }

    public function getTimeZone(): string
    {
        return $this->timeZone;
    }

    public function setTimeZone(string $timeZone): City
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
     *
     * @return City
     */
    public function setNameTranslations(array $nameTranslations): City
    {
        $this->nameTranslations = $nameTranslations;

        return $this;
    }

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function setCountry(?Country $country): City
    {
        $this->country = $country;

        return $this;
    }
}
