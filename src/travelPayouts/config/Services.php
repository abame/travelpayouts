<?php

declare(strict_types=1);

namespace TravelPayouts\config;

use TravelPayouts\Components\ServiceInterface;
use TravelPayouts\Services\DataService;
use TravelPayouts\Services\FlightService;
use TravelPayouts\Services\HotelSearchService;
use TravelPayouts\Services\HotelService;
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
            ServiceInterface::HOTEL_SERVICE => HotelService::class,
            ServiceInterface::HOTEL_SEARCH_SERVICE => HotelSearchService::class,
        ];
    }
}
