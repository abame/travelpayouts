<?php

namespace Tests\TravelPayouts\Services;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use TravelPayouts\Components\BaseClient;
use TravelPayouts\Components\HotelClient;

trait BaseServiceTrait
{
    /**
     * @return ObjectProphecy|ClientInterface|BaseClient
     * @throws GuzzleException
     */
    protected function getClient(string $dataName, bool $isBaseClient = false, bool $isHotelClient = false)
    {
        $data = $this->getData($dataName);
        /** @var ObjectProphecy|StreamInterface $stream */
        $stream = $this->prophesize(StreamInterface::class);
        $stream->getContents()->willReturn($data);

        /** @var ObjectProphecy|ResponseInterface $response */
        $response = $this->prophesize(ResponseInterface::class);
        $response->getBody()->willReturn($stream);

        /** @var ObjectProphecy|ClientInterface $client */
        $client = $this->prophesize(ClientInterface::class);
        $client->request(Argument::type('string'), Argument::type('string'), Argument::type('array'))
            ->willReturn($response->reveal());

        if ($isBaseClient || $isHotelClient) {
            /** @var ObjectProphecy|BaseClient|HotelClient $baseClient */
            $baseClient = $this->prophesize(BaseClient::class);
            if ($isHotelClient) {
                $baseClient = $this->prophesize(HotelClient::class);
            }
            $baseClient->getToken()->willReturn('DUMMY_TOKEN');
            $baseClient->setApiVersion(Argument::type('string'));
            $baseClient->getClient()->willReturn($client->reveal());
            $baseClient->execute(Argument::type('string'), Argument::type('array'))->willReturn(json_decode($data, true));
            $baseClient->executeJson(Argument::type('string'))->willReturn(json_decode($data, true));
            return $baseClient;
        }

        return $client;
    }

    private function getData(string $dataName): ?string
    {
        $path = realpath(sprintf(__DIR__ . '/../data/%s.json', $dataName));
        $data = file_get_contents(is_string($path) ? $path : '');
        return is_string($data) ? $data : null;
    }
}
