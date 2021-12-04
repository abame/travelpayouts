<?php

declare(strict_types=1);

namespace TravelPayouts\Components;

use BadMethodCallException;
use Exception;
use RuntimeException;
use TravelPayouts\config\Services;
use TravelPayouts\Services\DataService;
use TravelPayouts\Services\FlightService;
use TravelPayouts\Services\HotelsSearchService;
use TravelPayouts\Services\HotelsService;
use TravelPayouts\Services\HotelsServiceInterface;
use TravelPayouts\Services\PartnerService;
use TravelPayouts\Services\TicketsService;

/**
 * @method DataService         getDataService()
 * @method FlightService       getFlightService()
 * @method PartnerService      getPartnerService()
 * @method TicketsService      getTicketsService()
 * @method HotelsService       getHotelsService()
 * @method HotelsSearchService getHotelsSearchService()
 */
trait ServiceInjector
{
    /** @var array<string, string> */
    private array $serviceMap = [];

    /** @return array<string, string> */
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

    /** @throws Exception */
    private function getService(string $serviceName): ServiceInterface
    {
        if (!method_exists($this, 'getHotelClient') && !method_exists($this, 'getClient')) {
            throw new RuntimeException('No HTTP Client specified');
        }

        /** @var ServiceInterface $service */
        $service = new $serviceName();

        if ($service instanceof HotelsServiceInterface && method_exists($this, 'getHotelClient')) {
            $service->setClient($this->getHotelClient());
        }

        if (!($service instanceof HotelsServiceInterface) && method_exists($this, 'getClient')) {
            $service->setClient($this->getClient());
        }

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
