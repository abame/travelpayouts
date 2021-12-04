<?php

declare(strict_types=1);

namespace TravelPayouts\Entity;

class Country
{
    private string $iata;

    private string $name;

    private string $currency;

    /** @var array<string, string> */
    private $nameTranslations;

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

    public function setIata(string $iata): Country
    {
        $this->iata = $iata;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Country
    {
        $this->name = $name;

        return $this;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): Country
    {
        $this->currency = $currency;

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
     * @return Country
     */
    public function setNameTranslations(array $nameTranslations): Country
    {
        $this->nameTranslations = $nameTranslations;

        return $this;
    }
}
