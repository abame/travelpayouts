<?php

declare(strict_types=1);

namespace TravelPayouts;

use TravelPayouts\Components\Client;
use TravelPayouts\Components\HotelClient;
use TravelPayouts\Services\AbstractService;

class Travel extends AbstractService
{
    private Client $client;

    private HotelClient $hotelClient;

    private string $token;

    public function __construct(string $token = '')
    {
        if ($token !== '') {
            $this->setToken($token);
        }
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function getHotelClient(): HotelClient
    {
        return $this->hotelClient;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;
        $this->init();

        return $this;
    }

    private function init(): void
    {
        $this->client = new Client($this->getToken());
        $this->hotelClient = new HotelClient($this->getToken());
    }
}
