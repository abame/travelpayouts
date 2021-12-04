<?php

declare(strict_types=1);

namespace TravelPayouts\Entity;

/**
 * Class Airport
 */
class HotelSmall
{
    private int $id;

    private string $label;

    private string $locationName;

    private int $locationId;

    private string $fullName;

    /** @var array<string, float> */
    private array $location = [];

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): HotelSmall
    {
        $this->id = $id;

        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): HotelSmall
    {
        $this->label = $label;

        return $this;
    }

    public function getLocationName(): string
    {
        return $this->locationName;
    }

    public function setLocationName(string $locationName): HotelSmall
    {
        $this->locationName = $locationName;

        return $this;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): HotelSmall
    {
        $this->fullName = $fullName;

        return $this;
    }

    /** @return array<string, float> */
    public function getLocation(): array
    {
        return $this->location;
    }

    /** @param array<string, float> $location */
    public function setLocation(array $location): HotelSmall
    {
        $this->location = $location;

        return $this;
    }

    public function getLocationId(): int
    {
        return $this->locationId;
    }

    public function setLocationId(int $locationId): HotelSmall
    {
        $this->locationId = $locationId;

        return $this;
    }
}
