<?php

declare(strict_types=1);

namespace TravelPayouts\Services;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use TravelPayouts\Components\Client;
use TravelPayouts\Components\HotelsClient;
use TravelPayouts\Components\ServiceInterface;
use TravelPayouts\Entity\Hotel;
use TravelPayouts\Entity\HotelLocation;
use TravelPayouts\Entity\HotelLocationSmall;
use TravelPayouts\Entity\HotelSmall;

class HotelsService extends AbstractService implements ServiceInterface, HotelsServiceInterface
{
    private HotelsClient $client;

    /** @var string[] */
    private array $availableLanguages = [
        'pt',
        'en',
        'fr',
        'de',
        'id',
        'it',
        'pl',
        'es',
        'th',
        'ru',
    ];

    public function searchHotels(
        string $query,
        bool $searchByCoordinates,
        string $lookFor = self::BOTH_TYPE,
        string $lang = 'en',
        int $limit = 30,
        bool $convertCase = true
    ): array {
        $arResult = ['hotels' => [], 'locations' => []];
        $url = 'lookup';

        $options = [
            'query' => $query,
            'lang' => $lang,
            'lookFor' => $lookFor,
            'limit' => $limit,
            'convertCase' => $convertCase,
        ];

        /** @var array{status: string, results: array<string, array<int, array<string, string|int|array<int, string>>>>} $response */
        $response = $this->client->execute($url, $options);

        foreach ($response['results'] as $resultType => $resultSet) {
            switch ($resultType) {
                case 'locations':
                    foreach ($resultSet as $item) {
                        $arResult['locations'][] = $searchByCoordinates ? $this->createHotelLocationSmall($item) : $this->createHotelLocation($item);
                    }
                    break;
                case 'hotels':
                    /** @var array<string, string|int|array<string, float>> $item */
                    foreach ($resultSet as $item) {
                        $label = array_key_exists('label', $item) ? $item['label'] : $item['name'];
                        $label = is_string($label) ? $label : '';
                        $model = (new HotelSmall())
                            ->setId(is_numeric($item['id']) ? (int)$item['id'] : 0)
                            ->setFullName(is_string($item['fullName']) ? $item['fullName'] : '')
                            ->setLocation(is_array($item['location']) ? $item['location'] : [])
                            ->setLabel($label)
                            ->setLocationId(is_numeric($item['locationId']) ? (int)$item['locationId'] : 0)
                            ->setLocationName(is_string($item['locationName']) ? $item['locationName'] : '');

                        $arResult['hotels'][] = $model;
                    }
                    break;
            }
        }

        return $arResult;
    }

    public function getClient()
    {
        return $this->client;
    }

    public function setClient($client): self
    {
        if (!($client instanceof HotelsClient)) {
            throw new Exception(sprintf('Client of class %s is not allowed here', get_class($client)));
        }

        $this->client = $client;
        return $this;
    }

    public function getCostOfLiving(
        string $location,
        string $checkIn,
        string $checkOut,
        string $currency = 'eur',
        int $locationId = null,
        int $hotelId = null,
        string $hotel = null,
        int $adults = 2,
        int $children = 0,
        int $infants = 0,
        int $limit = 4,
        string $customerIp = null
    ): array {
        $url = 'cache';

        $options = [
            'location' => $location,
            'checkIn' => $checkIn,
            'checkOut' => $checkOut,
            'adults' => $adults,
            'children' => $children,
            'infants' => $infants,
            'limit' => $limit,
            'currency' => $currency
        ];

        if ($locationId) {
            $options['locationId'] = $locationId;
        }

        if ($hotelId) {
            $options['hotelId'] = $hotelId;
        }

        if ($hotel) {
            $options['hotel'] = $hotel;
        }

        if ($customerIp) {
            $options['customerIp'] = $customerIp;
        }

        /** @var array<int, array<string, int|string|array<int|string, float|string|null>>> $response */
        $response = $this->client->execute($url, $options);

        $dataService = $this->getDataService()->setClient(new Client($this->client->getToken()));

        /** @var array<int, array<int|string, HotelLocationSmall|array<array-key, HotelLocationSmall|array<int|string, float|null|string>|float|int|null|string>|int|string>> $responseData */
        $responseData = [];
        foreach ($response as $type => $value) {
            $isLocationType = $type == 'location';
            /** @var array<string, mixed> $locationArray */
            $locationArray = $value['location'] ?? [];

            /** @var string|null $countryName */
            $countryName = $locationArray['country'] ?? null;
            $countryName = $countryName === null && $isLocationType ? $value['country'] : null;
            if (!is_string($countryName)) {
                continue;
            }
            $locationModel = $this->getLocationModel($dataService, $countryName, $value, $locationArray);

            $singleResponse = $isLocationType ? $response : $value;
            $singleResponse['location'] = $locationModel;
            $responseData[] = $singleResponse;
        }

        return $responseData;
    }

    public function getHotelsSelection(
        string $checkIn,
        string $checkOut,
        string $type,
        int $id,
        string $currency = 'usd',
        string $language = 'en',
        int $limit = 10
    ): array {
        $url = 'public/widget_location_dump';

        $options = [
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'currency' => $currency,
            'language' => $language,
            'limit' => $limit,
            'type' => $type,
            'id' => $id,
        ];

        if (!in_array($language, $this->availableLanguages)) {
            throw new InvalidArgumentException(sprintf(
                '%s is not a valid language. Possible options: %s',
                $language,
                var_export($this->availableLanguages, true)
            ));
        }

        /** @var array{popularity: array<int, array<string, int|string>>} $response */
        $response =  $this->client->execute($url, $options);
        return $response;
    }

    public function getHotelCollectionsTypes(int $id): array
    {
        $url = 'public/available_selections';

        $response = $this->client->execute($url, ['id' => $id]);
        if (!is_array($response)) {
            throw new Exception('Response is not valid');
        }
        return $response;
    }

    public function getHotelsListByLocation(int $id): array
    {
        $arResult = [];

        $url = 'static/hotels';

        $response = $this->client->execute($url, ['locationId' => $id]);
        if (!is_array($response)) {
            throw new Exception('Response is not valid');
        }

        foreach ($response['hotels'] as $hotel) {
            $model = new Hotel();
            $model->setAttributes($hotel);

            $arResult[] = $model;
        }

        return $arResult;
    }

    public function getRoomTypes(string $language = 'en'): array
    {
        $url = 'static/roomTypes';

        if (!in_array($language, $this->availableLanguages)) {
            throw new InvalidArgumentException(sprintf(
                '%s is not a valid language. Possible options: %s',
                $language,
                var_export($this->availableLanguages, true)
            ));
        }

        $response = $this->client->execute($url, ['language' => $language]);
        if (!is_array($response)) {
            throw new Exception('Response is not valid');
        }
        return $response;
    }

    public function getHotelsTypes(string $language = 'en'): array
    {
        $url = 'static/hotelTypes';

        if (!in_array($language, $this->availableLanguages)) {
            throw new InvalidArgumentException(sprintf(
                '%s is not a valid language. Possible options: %s',
                $language,
                var_export($this->availableLanguages, true)
            ));
        }

        $response = $this->client->execute($url, ['language' => $language]);
        if (!is_array($response)) {
            throw new Exception('Response is not valid');
        }
        return $response;
    }

    public function getHotelPhoto(int $hotelId, int $photoId, string $photoSize, bool $auto = false): string
    {
        $url_example = 'https://cdn.photo.hotellook.com/image_v2/crop/h%s_%s/%s.';

        $url_example .= $auto ? 'auto' : 'jpg';

        return sprintf($url_example, $hotelId, $photoId, $photoSize);
    }

    /** @param array<string, array<int, string>|int|string> $item */
    private function createHotelLocation(array $item): HotelLocation
    {
        return (new HotelLocation())
            ->setId((int)$item['id'])
            ->setCityName(is_string($item['cityName']) ? $item['cityName'] : '')
            ->setIata(is_array($item['iata']) ? $item['iata'] : [])
            ->setLocation(is_array($item['location']) ? $item['location'] : [])
            ->setFullName(is_string($item['fullName']) ? $item['fullName'] : '')
            ->setCountryCode(is_string($item['countryCode']) ? $item['countryCode'] : '')
            ->setCountryName(is_string($item['countryName']) ? $item['countryName'] : '')
            ->setHotelsCount((int)$item['hotelsCount'])
            ->setScore(is_string($item['_score']) ? $item['_score'] : '');
    }

    /** @param array<string, array<int, string>|int|string> $item */
    private function createHotelLocationSmall(array $item): HotelLocationSmall
    {
        /** @var array<string, float> $geo */
        $geo = is_array($item['geo']) ? $item['geo'] : [];
        return (new HotelLocationSmall())
            ->setId((int)$item['id'])
            ->setName(is_string($item['name']) ? $item['name'] : '')
            ->setCountryIso(is_string($item['countryIso']) ? $item['countryIso'] : '')
            ->setState(is_string($item['state']) ? $item['state'] : null)
            ->setType(is_string($item['type']) ? $item['type'] : '')
            ->setGeo($geo)
            ->setFullName(is_string($item['fullName']) ? $item['fullName'] : '');
    }

    /**
     * @param DataService $dataService
     * @param string $countryName
     * @param array<string, array<int|string, float|string|null>|int|string|HotelLocationSmall> $value
     * @param array<string, mixed> $locationArray
     * @return HotelLocationSmall
     * @throws GuzzleException
     */
    private function getLocationModel(DataService $dataService, string $countryName, array $value, array $locationArray): HotelLocationSmall
    {
        $country = $dataService->getCountryByName($countryName);

        $name = isset($value['name']) && is_string($value['name']) ? $value['name'] : '';
        $name = $name === '' && is_string($locationArray['name']) ? $locationArray['name'] : '';

        $state = isset($value['state']) && is_string($value['state']) ? $value['state'] : '';
        $state = $state === '' && isset($locationArray['state']) && is_string($locationArray['state']) ? $locationArray['state'] : '';

        $geo = isset($value['geo']) && is_array($value['geo']) ? $value['geo'] : [];

        /** @var array<string, float> $geo */
        $geo = count($geo) === 0 && is_array($locationArray['geo']) ? $locationArray['geo'] : [];

        $countryIso = is_array($country) && is_string($country['code']) ? $country['code'] : '';

        return (new HotelLocationSmall())
            ->setGeo($geo)
            ->setName($name)
            ->setState($state)
            ->setCountryIso($countryIso);
    }
}
