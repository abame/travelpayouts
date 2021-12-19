<?php

declare(strict_types=1);

namespace TravelPayouts\Components;

use BadMethodCallException;
use Exception;
use RuntimeException;
use TravelPayouts\config\Services;
use TravelPayouts\Services\DataService;
use TravelPayouts\Services\FlightService;
use TravelPayouts\Services\HotelSearchService;
use TravelPayouts\Services\HotelSearchServiceInterface;
use TravelPayouts\Services\HotelService;
use TravelPayouts\Services\HotelServiceInterface;
use TravelPayouts\Services\TicketsService;

/**
 * @method DataService         getDataService()
 * @method FlightService       getFlightService()
 * @method TicketsService      getTicketsService()
 * @method HotelService       getHotelService()
 * @method HotelSearchService getHotelSearchService()
 */
trait ServiceInjector
{
    /** @var array<string, class-string> */
    private array $serviceMap = [];

    /** @return array<string, class-string> */
    private function getServiceMap(): array
    {
        if (count($this->serviceMap) === 0) {
            $services = new Services();

            foreach ($services->services() as $serviceName => $serviceClass) {
                $methodName = 'get' . ucfirst($serviceName);
                $this->serviceMap[$methodName] = $serviceClass;
            }
        }

        return $this->serviceMap;
    }

    /**
     * @param class-string $serviceName
     * @throws Exception|RuntimeException
     */
    private function getService(string $serviceName): ServiceInterface
    {
        if (!method_exists($this, 'getHotelClient') && !method_exists($this, 'getClient')) {
            throw new RuntimeException('No HTTP Client specified');
        }

        /** @var ServiceInterface $service */
        $service = new $serviceName();

        /** @var BaseClient|null $client */
        $client = null;
        if (($service instanceof HotelServiceInterface || $service instanceof HotelSearchServiceInterface) && method_exists($this, 'getHotelClient')) {
            $client = $this->getHotelClient();
        }

        if (!($service instanceof HotelServiceInterface || $service instanceof HotelSearchServiceInterface) && method_exists($this, 'getClient')) {
            $client = $this->getClient();
        }
        if ($client === null || $client instanceof ServiceInterface) {
            throw new RuntimeException('No HTTP Client specified');
        }
        $service->setClient($client);

        return $service;
    }

    /**
     * @param array<int|string, mixed> $args
     * @return ServiceInterface
     * @throws BadMethodCallException|Exception
     */
    public function __call(string $name, array $args)
    {
        if (array_key_exists($name, $this->getServiceMap())) {
            return $this->getService($this->serviceMap[$name]);
        }

        throw new BadMethodCallException(sprintf('Calling unknown method: ' . get_class($this) . '::%s()', $name));
    }
}
