<?php

declare(strict_types=1);

namespace TravelPayouts\Components;

use Exception;

interface ServiceInterface
{
    public const DATA_SERVICE = 'DataService';
    public const FLIGHT_SERVICE = 'FlightService';
    public const PARTNER_SERVICE = 'PartnerService';
    public const TICKETS_SERVICE = 'TicketsService';
    public const HOTEL_SERVICE = 'HotelService';
    public const HOTEL_SEARCH_SERVICE = 'HotelSearchService';

    /**
     * @return BaseClient
     * @throws Exception
     */
    public function getClient();

    /**
     * @param BaseClient $client
     * @throws Exception
     */
    public function setClient($client): self;
}
