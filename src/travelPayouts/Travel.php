<?php

declare(strict_types=1);

namespace TravelPayouts;

use TravelPayouts\Components\Client;
use TravelPayouts\Components\HotelsClient;
use TravelPayouts\Components\ServiceInjector;

class Travel
{
    use ServiceInjector;

    private Client $client;

    private HotelsClient $hotelsClient;

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

    public function getHotelClient(): HotelsClient
    {
        return $this->hotelsClient;
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
        $this->hotelsClient = new HotelsClient($this->getToken());
    }
}
