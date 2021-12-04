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
    public const HOTELS_SERVICE = 'HotelsService';
    public const HOTELS_SEARCH_SERVICE = 'HotelsSearchService';

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
