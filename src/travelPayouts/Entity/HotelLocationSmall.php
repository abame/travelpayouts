<?php

declare(strict_types=1);

namespace TravelPayouts\Entity;

class HotelLocationSmall
{
    private int $id;

    private string $type;

    private string $countryIso;

    private string $name;

    private ?string $state;

    private string $fullName;

    /** @var array<string, float> */
    private array $geo;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): HotelLocationSmall
    {
        $this->id = $id;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): HotelLocationSmall
    {
        $this->type = $type;

        return $this;
    }

    public function getCountryIso(): string
    {
        return $this->countryIso;
    }

    public function setCountryIso(string $countryIso): HotelLocationSmall
    {
        $this->countryIso = $countryIso;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): HotelLocationSmall
    {
        $this->name = $name;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): HotelLocationSmall
    {
        $this->state = $state;

        return $this;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): HotelLocationSmall
    {
        $this->fullName = $fullName;

        return $this;
    }

    /** @return array<string, float> */
    public function getGeo(): array
    {
        return $this->geo;
    }

    /** @param array<string, float> $geo */
    public function setGeo(array $geo): HotelLocationSmall
    {
        $this->geo = $geo;

        return $this;
    }
}
