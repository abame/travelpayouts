<?php

declare(strict_types=1);

namespace TravelPayouts\Services;

use DateTime;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use TravelPayouts\Components\AbstractService;
use TravelPayouts\Components\BaseClient;
use TravelPayouts\Components\ServiceInterface;
use TravelPayouts\Entity\Airport;
use TravelPayouts\Entity\City;
use TravelPayouts\Entity\Ticket;

class TicketsService extends AbstractService implements ServiceInterface, TicketsServiceInterface
{
    private BaseClient $client;

    public function getLatestPrices(
        string $origin = '',
        string $destination = '',
        bool $one_way = false,
        string $currency = 'eur',
        string $period_type = 'year',
        int $page = 1,
        int $limit = 30,
        bool $show_to_affiliates = true,
        string $sorting = 'price',
        int $trip_class = self::ECONOMY_CLASS,
        int $trip_duration = 0
    ): array {
        $url = 'prices/latest';

        $options = [
            'origin' => strlen($origin) > 0 ? $origin : null,
            'destination' => strlen($destination) > 0 ? $destination : null,
            'one_way' => $one_way,
            'currency' => $currency,
            'period_type' => in_array($period_type, ['year', 'month', 'seasson', 'day'], true) ? $period_type : 'year',
            'page' => $page,
            'limit' => $limit,
            'show_to_affiliates' => $show_to_affiliates,
            'sorting' => $sorting,
            'trip_class' => $trip_class,
            'trip_duration' => $trip_duration > 0 ? $trip_duration : null,
        ];

        /** @var array{success: bool, currency: string, error: string, data: array<int, array<string, string|int|bool>>} $response */
        $response = $this->getClient()->execute($url, $options);

        return $this->mapTickets($response, $currency);
    }

    public function getClient(): BaseClient
    {
        return $this->client;
    }

    public function setClient($client): self
    {
        if (!($client instanceof BaseClient)) {
            throw new Exception(sprintf('Client of class %s is not allowed here', get_class($client)));
        }

        $this->client = $client;
        return $this;
    }

    public function getMonthMatrix(
        string $origin,
        string $destination,
        string $month,
        string $currency = 'eur',
        bool $show_to_affiliates = true
    ): array {
        $url = 'prices/month-matrix';

        $date = new DateTime($month);

        $options = [
            'currency' => $currency,
            'origin' => $origin,
            'destination' => $destination,
            'show_to_affiliates' => $show_to_affiliates,
            'month' => $date->format('Y-m-d'),
        ];

        /** @var array{success: bool, currency: string, data: array<int, array<string, string|int|bool>>} $response */
        $response = $this->getClient()->execute($url, $options);

        return $this->mapTickets($response, $currency);
    }

    public function getNearestPlacesMatrix(
        string $depart_date,
        string $return_date,
        string $origin = '',
        string $destination = '',
        string $currency = 'eur',
        bool $show_to_affiliates = true
    ): array {
        /** @var array{prices: Ticket[], origins: array<array-key, Airport|null>, destinations: array<array-key, Airport|null>} $arResult */
        $arResult = [];

        $url = 'prices/nearest-places-matrix';

        $depart_date = new DateTime($depart_date);
        $return_date = new DateTime($return_date);

        $options = [
            'currency' => $currency,
            'origin' => $origin,
            'destination' => $destination,
            'show_to_affiliates' => $show_to_affiliates,
            'depart_date' => $depart_date->format('Y-m-d'),
            'return_date' => $return_date->format('Y-m-d'),
        ];

        /** @var array{prices: array<int, array<string, string|int>>, origins: string[], destinations: string[]} $response */
        $response = $this->getClient()->execute($url, $options);

        $dataService = $this->getDataService();

        $arResult['origins'] = array_map(function (string $iata) use ($dataService) {
            return $dataService->getAirport($iata);
        }, $response['origins']);

        $arResult['destinations'] = array_map(function ($iata) use ($dataService) {
            return $dataService->getAirport($iata);
        }, $response['destinations']);

        $tickets = [];
        foreach ($response['prices'] as $ticket) {
            $origin = $dataService->getPlace((string)$ticket['origin']);
            $destination = $dataService->getPlace((string)$ticket['destination']);
            if ($origin !== null && $destination !== null) {
                $tickets[] = $this->createTicketObject($origin, $destination, $ticket, $currency);
            }
        }

        $arResult['prices'] = $tickets;

        return $arResult;
    }

    public function getWeekMatrix(
        string $origin,
        string $destination,
        string $depart_date,
        string $return_date,
        string $currency = 'eur',
        bool $show_to_affiliates = true
    ): array {
        $url = 'prices/week-matrix';

        $depart_date = new DateTime($depart_date);
        $return_date = new DateTime($return_date);

        $options = [
            'currency' => $currency,
            'origin' => $origin,
            'destination' => $destination,
            'show_to_affiliates' => $show_to_affiliates,
            'depart_date' => $depart_date->format('Y-m-d'),
            'return_date' => $return_date->format('Y-m-d'),
        ];

        /** @var array{success: bool, currency: string, data: array<int, array<string, int|string>>} $response */
        $response = $this->getClient()->execute($url, $options);

        return $this->mapTickets($response, $currency);
    }

    public function getCalendar(
        string $origin,
        string $destination,
        string $depart_date,
        string $return_date = '',
        string $currency = 'eur',
        string $calendar_type = 'departure_date',
        int $trip_duration = 0
    ): array {
        $url = 'prices/calendar';

        $depart_date = new DateTime($depart_date);
        $return_date = strlen($return_date) > 0 ? new DateTime($return_date) : false;

        $options = [
            'currency' => in_array($currency, ['usd', 'eur'], true) ? $currency : 'eur',
            'origin' => $origin,
            'destination' => $destination,
            'depart_date' => $depart_date->format('Y-m'),
            'return_date' => $return_date ? $return_date->format('Y-m') : null,
            'trip_duration' => $trip_duration > 0 ? $trip_duration : null,
            'calendar_type' => in_array($calendar_type, ['departure_date', 'return_date'], true) ? $calendar_type : null,
        ];

        $this->getClient()->setApiVersion('v1');
        /** @var array{success: bool, currency: string, data: array<string, array<string, int|string>>} $response */
        $response = $this->getClient()->execute($url, $options);

        return $this->mapTickets($response, $currency);
    }

    public function getCheap(string $origin, string $destination, string $depart_date = '', string $return_date = '', string $currency = 'eur'): array
    {
        $url = 'prices/cheap';

        /** @var array{success: bool, currency: string, data: array<string, array<int, array<string, int|string>>>} $response */
        $response = $this->getResponse($depart_date, $return_date, $currency, $origin, $destination, $url);

        $dataService = $this->getDataService();

        $tickets = [];
        /** @var array{price: int, airline: string, flight_number: int, departure_at: string, return_at: string, expires_at: string} $ticket */
        foreach ($response['data'][$destination] as $ticket) {
            $destinationObj = $dataService->getPlace($destination);
            $originObj = $dataService->getPlace($origin);
            if ($originObj !== null && $destinationObj !== null) {
                $tickets[] = (new Ticket())
                    ->setValue($ticket['price'])
                    ->setDestination($destinationObj)
                    ->setOrigin($originObj)
                    ->setCurrency($currency)
                    ->setDepartDate(new DateTime($ticket['departure_at']))
                    ->setReturnDate(new DateTime($ticket['return_at']))
                    ->setExpires(new DateTime($ticket['expires_at']))
                    ->setAirline($ticket['airline'])
                    ->setFlightNumber($ticket['flight_number']);
            }
        }

        return $tickets;
    }

    public function getDirect(string $origin, string $destination, string $depart_date = '', string $return_date = '', string $currency = 'eur'): ?Ticket
    {
        $url = 'prices/direct';

        /** @var array{success: bool, currency: string, data: array<string, array<int, array<string, string|int>>>} $response */
        $response = $this->getResponse($depart_date, $return_date, $currency, $origin, $destination, $url);

        /** @var array<int, array<string, string|int>> $item */
        $item = array_shift($response['data']);

        if ((count($item) === 0 && $response['success'])) {
            return null;
        }

        $dataService = $this->getDataService();
        $destination = $dataService->getPlace($destination);
        $origin = $dataService->getPlace($origin);
        if ($origin === null || $destination === null) {
            return null;
        }

        return $this->createTicketObject($origin, $destination, $item[0], $currency);
    }

    public function getMonthly(string $origin, string $destination, string $currency = 'eur'): array
    {
        $url = 'prices/monthly';

        $options = [
            'currency' => in_array($currency, ['usd', 'eur'], true) ? $currency : 'eur',
            'origin' => $origin,
            'destination' => $destination,
        ];

        $this->getClient()->setApiVersion('v1');
        /** @var array{success: bool, currency: string, data: array<string, array<string, string|int>>} $response */
        $response = $this->getClient()->execute($url, $options);

        return $this->mapTickets($response, $currency);
    }

    public function getPopularRoutesFromCity(string $origin): array
    {
        $url = 'city-directions';

        $options = [
            'origin' => $origin,
        ];

        /** @var array{success: bool, currency: string, data: array<string, array<int, array<string, string|int>>>} $response */
        $response = $this->getClient()->setApiVersion('v1')->execute($url, $options);

        $dataService = $this->getDataService();

        $tickets = [];
        /** @var array{price: int, destination: string, origin: string, departure_at: string, flight_number: int, airline: string, return_at: string, transfers: int, expires_at: string} $ticket */
        foreach ($response['data'] as $ticket) {
            $destination = $dataService->getPlace($ticket['destination']);
            $originObj = $dataService->getPlace($ticket['origin']);
            if ($originObj !== null && $destination !== null) {
                $tickets[] = $this->createTicketObject($originObj, $destination, $ticket, 'eur');
            }
        }

        return $tickets;
    }

    public function getAirlineDirections(string $airline_code, int $limit = 30): array
    {
        /** @var array<int, array{origin: City|Airport|null, destination: City|Airport|null, rating: int}> $arResult */
        $arResult = [];
        $url = 'airline-directions';

        $options = [
            'airline_code' => $airline_code,
            'limit' => $limit,
        ];

        $this->getClient()->setApiVersion('v1');

        /** @var array{success: bool, currency: string, error: string|null, data: array<string, int>} $response */
        $response = $this->getClient()->execute($url, $options);

        $dataService = $this->getDataService();

        foreach ($response['data'] as $direction => $rating) {
            list($origin, $destination) = explode('-', $direction);

            $arResult[] = [
                'origin' => $dataService->getPlace($origin),
                'destination' => $dataService->getPlace($destination),
                'rating' => $rating,
            ];
        }

        return $arResult;
    }

    /**
     * @param array<string, bool|string|array<int|string, array<string, string|int|bool>>> $response
     * @return  Ticket[]
     * @throws Exception|GuzzleException
     */
    private function mapTickets(array $response, string $currency): array
    {
        $dataService = $this->getDataService();
        $data = $response['data'];
        if (!is_array($data)) {
            return [];
        }
        $tickets = [];
        foreach ($data as $ticket) {
            $destination = $dataService->getPlace((string)$ticket['destination']);
            $origin = $dataService->getPlace((string)$ticket['origin']);
            if ($destination !== null && $origin !== null) {
                $tickets[] = $this->createTicketObject($origin, $destination, $ticket, $currency);
            }
        }
        return $tickets;
    }

    /**
     * @param City|Airport $origin
     * @param City|Airport $destination
     * @param array<string, int|string|bool> $ticket
     * @throws Exception
     */
    private function createTicketObject($origin, $destination, array $ticket, string $currency): Ticket
    {
        return (new Ticket())
            ->setValue(isset($ticket['price']) ? (int)$ticket['price'] : (int)$ticket['value'])
            ->setDestination($destination)
            ->setOrigin($origin)
            ->setAirline(isset($ticket['airline']) ? (string)$ticket['airline'] : '')
            ->setFlightNumber((int)($ticket['flight_number'] ?? 0))
            ->setExpires(isset($ticket['expires_at']) ? new DateTime((string)$ticket['expires_at']) : null)
            ->setCurrency($currency)
            ->setActual(isset($ticket['actual']) && $ticket['actual'])
            ->setDepartDate(new DateTime(isset($ticket['departure_at']) ? (string)$ticket['departure_at'] : (string)$ticket['depart_date']))
            ->setReturnDate(new DateTime(isset($ticket['return_at']) ? (string)$ticket['return_at'] : (string)$ticket['return_date']))
            ->setFoundAt(new DateTime(isset($ticket['found_at']) ? (string)$ticket['found_at'] : 'now'))
            ->setNumberOfChanges(isset($ticket['transfers']) ? (int)$ticket['transfers'] : (isset($ticket['number_of_changes']) ? (int)$ticket['number_of_changes'] : 0))
            ->setDistance(isset($ticket['distance']) ? (int)$ticket['distance'] : 0)
            ->setShowToAffiliates(!isset($ticket['show_to_affiliates']) || $ticket['show_to_affiliates'])
            ->setTripClass(isset($ticket['trip_class']) ? (int)$ticket['trip_class'] : self::ECONOMY_CLASS);
    }

    /**
     * @return mixed
     * @throws Exception|GuzzleException
     */
    private function getResponse(string $depart_date, string $return_date, string $currency, string $origin, string $destination, string $url)
    {
        $depart = new DateTime($depart_date);
        $return = new DateTime($return_date);

        $depart = preg_match('/(\d{4}-\d{2}-\d{2})/', $depart_date) ? $depart->format('Y-m-d') : $depart->format('Y-m');
        $return = preg_match('/(\d{4}-\d{2}-\d{2})/', $depart_date) ? $return->format('Y-m-d') : $return->format('Y-m');

        $options = [
            'currency' => in_array($currency, ['usd', 'eur'], true) ? $currency : 'eur',
            'origin' => $origin,
            'destination' => $destination,
            'depart_date' => $depart_date !== '' ? $depart : null,
            'return_date' => $return_date !== '' ? $return : null,
        ];

        $this->getClient()->setApiVersion('v1');
        return $this->getClient()->execute($url, $options);
    }
}
