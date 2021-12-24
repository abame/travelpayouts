<?php

declare(strict_types=1);

namespace Tests\TravelPayouts\Services;

use DateInterval;
use DateTime;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use TravelPayouts\Entity\Hotel;
use TravelPayouts\Entity\HotelLocation;
use TravelPayouts\Entity\HotelSmall;
use TravelPayouts\Services\HotelService;
use TravelPayouts\Travel;

class HotelsServiceTest extends TestCase
{
    use ProphecyTrait;
    use BaseServiceTrait;

    private HotelService $service;

    public function testGetHotelTypes(): void
    {
        $client = $this->getClient('hotels/types', false, true);
        $this->service->setClient($client->reveal());
        $data = $this->service->getHotelsTypes();
        $this->assertCount(22, $data);
    }

    public function testGetRoomTypes(): void
    {
        $client = $this->getClient('hotels/room_types', false, true);
        $this->service->setClient($client->reveal());
        $data = $this->service->getRoomTypes();
        $this->assertCount(129, $data);
    }

    public function testSearchHotels(): void
    {
        $client = $this->getClient('hotels/search', false, true);
        $this->service->setClient($client->reveal());
        $data = $this->service->searchHotels('moscow', false);
        $this->assertArrayHasKey('hotels', $data);
        $this->assertInstanceOf(HotelSmall::class, $data['hotels'][0]);
        $this->assertArrayHasKey('locations', $data);
        $this->assertInstanceOf(HotelLocation::class, $data['locations'][0]);
    }

    public function testGetHotelsSelection(): void
    {
        $client = $this->getClient('hotels/selection', false, true);
        $this->service->setClient($client->reveal());
        $today = new DateTime('now');
        $tomorrow = clone $today;
        $tomorrow->add(new DateInterval('P1D'));
        $data = $this->service->getHotelsSelection($today->format('Y-m-d'), $tomorrow->format('Y-m-d'), 'popularity', 12209);
        $this->assertArrayHasKey('popularity', $data);
    }

    public function testGetCostOfLiving(): void
    {
        $client = $this->getClient('hotels/living_cost', false, true);
        $this->service->setClient($client->reveal());
        $today = new DateTime('now');
        $tomorrow = clone $today;
        $tomorrow->add(new DateInterval('P1D'));
        $data = $this->service->getCostOfLiving('moscow', $today->format('Y-m-d'), $tomorrow->format('Y-m-d'), 'eur', null, 277083);
        $this->assertCount(1, $data);
        $this->assertArrayHasKey('hotelName', $data[0]);
        $this->assertArrayHasKey('location', $data[0]);
    }

    public function testGetHotelCollectionTypes(): void
    {
        $client = $this->getClient('hotels/collection', false, true);
        $this->service->setClient($client->reveal());
        $data = $this->service->getHotelCollectionsTypes(1);
        $this->assertIsArray($data);
        $this->assertCount(19, $data);
    }

    public function testGetHotelAmenities(): void
    {
        $client = $this->getClient('hotels/amenities', false, true);
        $this->service->setClient($client->reveal());
        $data = $this->service->getHotelAmenities();
        $this->assertIsArray($data);
        $this->assertCount(140, $data);
        $this->assertArrayHasKey('id', $data[0]);
        $this->assertArrayHasKey('name', $data[0]);
        $this->assertArrayHasKey('groupName', $data[0]);
    }

    public function testGetHotelCountries(): void
    {
        $client = $this->getClient('hotels/countries', false, true);
        $this->service->setClient($client->reveal());
        $data = $this->service->getHotelCountries();
        $this->assertIsArray($data);
        $this->assertCount(237, $data);
        $this->assertArrayHasKey('id', $data[0]);
        $this->assertArrayHasKey('code', $data[0]);
        $this->assertArrayHasKey('name', $data[0]);
    }

    public function testGetHotelCities(): void
    {
        $client = $this->getClient('hotels/cities', false, true);
        $this->service->setClient($client->reveal());
        $data = $this->service->getHotelCities();
        $this->assertIsArray($data);
        $this->assertCount(1, $data);
        $this->assertArrayHasKey('state_code', $data[0]);
        $this->assertArrayHasKey('id', $data[0]);
        $this->assertArrayHasKey('latitude', $data[0]);
        $this->assertArrayHasKey('longitude', $data[0]);
        $this->assertArrayHasKey('name', $data[0]);
        $this->assertArrayHasKey('countryId', $data[0]);
        $this->assertArrayHasKey('code', $data[0]);
    }

    public function testGetHotelsListByLocationId(): void
    {
        $client = $this->getClient('hotels/locations', false, true);
        $this->service->setClient($client->reveal());
        $data = $this->service->getHotelsListByLocation(1);
        $this->assertIsArray($data);
        $this->assertInstanceOf(Hotel::class, $data[0]);
    }

    public function testGetHotelPhotoIds(): void
    {
        $client = $this->getClient('hotels/photo_ids', false, true);
        $this->service->setClient($client->reveal());
        $data = $this->service->getHotelPhotoIds('1,2');
        $this->assertIsArray($data);
        $this->assertCount(2, $data);
    }

    public function testGetHotelPhotoUrl(): void
    {
        $data = $this->service->getHotelPhotoUrl(1);
        $this->assertSame('https://photo.hotellook.com/image_v2/limit/1/800/520.jpg', $data);
    }

    public function testGetSpriteHotelRoomPhotoUrl(): void
    {
        $data = $this->service->getSpriteHotelRoomPhotoUrl(1, 1, '100x100', 2, '100x100');
        $this->assertSame('https://photo.hotellook.com/rooms/sprite/h1_1/100x100/1/100x100.jpg', $data);
    }

    public function testGetHotelRoomPhotoUrl(): void
    {
        $data = $this->service->getHotelRoomPhotoUrl(1, 1, 1, 100, 100);
        $this->assertSame('https://photo.hotellook.com/rooms/crop/h1_1_1/100/100.jpg', $data);
    }

    public function testGetHotelCityPhotoUrl(): void
    {
        $data = $this->service->getHotelCityPhotoUrl('100x100', 'FRA');
        $this->assertSame('https://photo.hotellook.com/static/cities/100x100/FRA.jpg', $data);
    }

    protected function setUp(): void
    {
        $travel = new Travel('DUMMY_TOKEN');

        $this->service = $travel->getHotelService();

        date_default_timezone_set('UTC');
    }
}
