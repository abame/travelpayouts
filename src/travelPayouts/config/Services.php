<?php

declare(strict_types=1);

namespace TravelPayouts\config;

use TravelPayouts\Components\ServiceInterface;
use TravelPayouts\Services\DataService;
use TravelPayouts\Services\FlightService;
use TravelPayouts\Services\HotelsSearchService;
use TravelPayouts\Services\HotelsService;
use TravelPayouts\Services\PartnerService;
use TravelPayouts\Services\TicketsService;

class Services
{
    /** @return array<string, class-string> */
    public function services(): array
    {
        return [
            ServiceInterface::DATA_SERVICE => DataService::class,
            ServiceInterface::FLIGHT_SERVICE => FlightService::class,
            ServiceInterface::PARTNER_SERVICE => PartnerService::class,
            ServiceInterface::TICKETS_SERVICE => TicketsService::class,
            ServiceInterface::HOTELS_SERVICE => HotelsService::class,
            ServiceInterface::HOTELS_SEARCH_SERVICE => HotelsSearchService::class,
        ];
    }
}
