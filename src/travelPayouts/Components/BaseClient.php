<?php

declare(strict_types=1);

namespace TravelPayouts\Components;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

abstract class BaseClient
{
    protected Client $client;

    private string $apiVersion = 'v2';

    /**
     * @param string $url
     * @param array<string, mixed> $options
     * @param string $type
     * @param bool|true $replaceOptions
     *
     * @return mixed
     * @throws GuzzleException
     */
    abstract public function execute(string $url, array $options, string $type = 'GET', bool $replaceOptions = true);

    /**
     * @param array<string, mixed> $params
     * @throws GuzzleException
     */
    public function validateResponse(string $url, array $params, string $type = 'GET'): StreamInterface
    {
        /** @var Response $res */
        $res = $this->client->request($type, $url, $params);

        $statusCode = $res->getStatusCode();
        $body = $res->getBody();

        if ($statusCode !== 200) {
            /** @var bool|array<string, string> $strBody */
            $strBody = json_decode((string)$body, true);

            $message = !is_bool($strBody) && isset($strBody['message']) ? $strBody['message'] : 'unknown';

            throw new RuntimeException(sprintf('%s:%s', $statusCode, $message));
        }
        return $body;
    }

    /**
     * @return array<string, mixed>
     * @throws GuzzleException|RuntimeException
     */
    public function executeJson(string $url): array
    {
        $body = $this->validateResponse($url, []);

        /** @var array<string, mixed>|bool $response */
        $response = json_decode($body->getContents(), true);
        if (is_bool($response)) {
            throw new RuntimeException(sprintf('Unable to decode json response: %s', $body->getContents()));
        }
        return $response;
    }

    public function getApiVersion(): string
    {
        return $this->apiVersion;
    }

    public function setApiVersion(string $apiVersion): self
    {
        $this->apiVersion = $apiVersion;

        return $this;
    }
}
