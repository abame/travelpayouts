<?php

declare(strict_types=1);

namespace TravelPayouts\Entity;

class Hotel
{
    private int $id;

    private int $cityId;

    private int $stars;

    private float $priceFrom;

    private float $rating;

    private int $popularity;

    private int $propertyType;

    private string $checkOut;

    private string $checkIn;

    private float $distance;

    private int $photoCount;

    /** @var string[] */
    private array $photos;

    /** @var string[] */
    private array $facilities;

    /** @var string[] */
    private array $shortFacilities;

    /** @var string[] */
    private array $photosByRoomType;

    /** @var string[] */
    private array $location;

    /** @var array<string, string> */
    private array $name;

    /** @var array<string, string> */
    private array $address;

    private string $link;

    private int $poiDistance;

    /** @var string[] */
    private array $pois;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): Hotel
    {
        $this->id = $id;

        return $this;
    }

    public function getCityId(): int
    {
        return $this->cityId;
    }

    public function setCityId(int $cityId): Hotel
    {
        $this->cityId = $cityId;

        return $this;
    }

    public function getStars(): int
    {
        return $this->stars;
    }

    public function setStars(int $stars): Hotel
    {
        $this->stars = $stars;

        return $this;
    }

    public function getPriceFrom(): float
    {
        return $this->priceFrom;
    }

    public function setPriceFrom(float $priceFrom): Hotel
    {
        $this->priceFrom = $priceFrom;

        return $this;
    }

    public function getRating(): float
    {
        return $this->rating;
    }

    public function setRating(float $rating): Hotel
    {
        $this->rating = $rating;

        return $this;
    }

    public function getPopularity(): int
    {
        return $this->popularity;
    }

    public function setPopularity(int $popularity): Hotel
    {
        $this->popularity = $popularity;

        return $this;
    }

    public function getPropertyType(): int
    {
        return $this->propertyType;
    }

    public function setPropertyType(int $propertyType): Hotel
    {
        $this->propertyType = $propertyType;

        return $this;
    }

    public function getCheckOut(): string
    {
        return $this->checkOut;
    }

    public function setCheckOut(string $checkOut): Hotel
    {
        $this->checkOut = $checkOut;

        return $this;
    }

    public function getCheckIn(): string
    {
        return $this->checkIn;
    }

    public function setCheckIn(string $checkIn): Hotel
    {
        $this->checkIn = $checkIn;

        return $this;
    }

    public function getDistance(): float
    {
        return $this->distance;
    }

    public function setDistance(float $distance): Hotel
    {
        $this->distance = $distance;

        return $this;
    }

    public function getPhotoCount(): int
    {
        return $this->photoCount;
    }

    public function setPhotoCount(int $photoCount): Hotel
    {
        $this->photoCount = $photoCount;

        return $this;
    }

    /** @return string[] */
    public function getPhotos(): array
    {
        return $this->photos;
    }

    /** @param string[] $photos */
    public function setPhotos(array $photos): Hotel
    {
        $this->photos = $photos;

        return $this;
    }

    /** @return string[] */
    public function getFacilities(): array
    {
        return $this->facilities;
    }

    /** @param string[] $facilities */
    public function setFacilities(array $facilities): Hotel
    {
        $this->facilities = $facilities;

        return $this;
    }

    /** @return string[] */
    public function getShortFacilities(): array
    {
        return $this->shortFacilities;
    }

    /** @param string[] $shortFacilities */
    public function setShortFacilities(array $shortFacilities): Hotel
    {
        $this->shortFacilities = $shortFacilities;

        return $this;
    }

    /** @return string[] */
    public function getPhotosByRoomType(): array
    {
        return $this->photosByRoomType;
    }

    /** @param string[] $photosByRoomType */
    public function setPhotosByRoomType(array $photosByRoomType): Hotel
    {
        $this->photosByRoomType = $photosByRoomType;

        return $this;
    }

    /** @return string[] */
    public function getLocation(): array
    {
        return $this->location;
    }

    /** @param string[] $location */
    public function setLocation(array $location): Hotel
    {
        $this->location = $location;

        return $this;
    }

    /** @return array<string, string> */
    public function getName(): array
    {
        return $this->name;
    }

    /** @param array<string, string> $name*/
    public function setName(array $name): Hotel
    {
        $this->name = $name;

        return $this;
    }

    /** @return array<string, string> */
    public function getAddress(): array
    {
        return $this->address;
    }

    /** @param array<string, string> $address*/
    public function setAddress(array $address): Hotel
    {
        $this->address = $address;

        return $this;
    }

    public function getLink(): string
    {
        return $this->link;
    }

    public function setLink(string $link): Hotel
    {
        $this->link = $link;

        return $this;
    }

    public function getPoiDistance(): int
    {
        return $this->poiDistance;
    }

    public function setPoiDistance(int $poiDistance): Hotel
    {
        $this->poiDistance = $poiDistance;

        return $this;
    }

    /** @return string[] */
    public function getPois(): array
    {
        return $this->pois;
    }

    /** @param string[] $pois */
    public function setPois(array $pois): Hotel
    {
        $this->pois = $pois;

        return $this;
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function setAttributes(array $attributes = []): void
    {
        foreach ($attributes as $attribute => $value) {
            $funcName = 'set' . ucfirst($attribute);

            if (method_exists($this, $funcName)) {
                $this->$funcName($value);
            }
        }
    }
}
