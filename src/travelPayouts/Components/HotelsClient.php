<?php

declare(strict_types=1);

namespace TravelPayouts\Components;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

class HotelsClient extends BaseClient
{
    public const YASEN_HOST = 'https://yasen.hotellook.com/';

    private const API_HOST = 'https://engine.hotellook.com/';

    private string $token;

    private string $defaultHost = self::API_HOST;

    public function __construct(string $token, string $defaultHost = self::API_HOST)
    {
        $this->token = $token;
        $this->defaultHost = $defaultHost;
        $this->client = new HttpClient(
            [
                'base_uri' => $defaultHost,
                'headers' =>
                    [
                        'Content-Type' => 'application/json',
                        'X-Access-Token' => $this->token,
                        'Accept-Encoding' => 'gzip,deflate,sdch',
                    ],
            ]
        );
    }

    /**
     * @param string $url
     * @param array<string, mixed> $options
     * @param string $type
     * @param bool|true $replaceOptions
     *
     * @return mixed
     * @throws GuzzleException
     */
    public function execute(string $url, array $options, string $type = 'GET', bool $replaceOptions = true)
    {
        $finalUrl = '/api/' . $this->getApiVersion() . '/' . $url . '.json';
        if ($this->defaultHost === self::YASEN_HOST) {
            $finalUrl = '/tp/' . $url . '.json';
        }

        $params = [
            'http_errors' => false,
            'query' => [
                'token' => $this->getToken()
            ]
        ];

        if ($replaceOptions) {
            $paramName = $type === 'GET' ? 'query' : 'body';
            $params[$paramName] = isset($params[$paramName]) && is_array($params[$paramName]) ? (array_merge($params[$paramName], $options)) : $options;
        }
        if (!$replaceOptions) {
            $params += $options;
        }

        $body = $this->validateResponse($finalUrl, $params, $type);

        return $this->makeApiResponse($body);
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getClient(): HttpClient
    {
        return $this->client;
    }

    /**
     * @param StreamInterface $jsonString
     *
     * @return array<int, string>
     * @throws RuntimeException
     */
    private function makeApiResponse(StreamInterface $jsonString): array
    {
        $data = json_decode($jsonString->getContents(), true);
        if (!$data) {
            throw new RuntimeException(sprintf('Unable to decode json response: %s', $jsonString->getContents()));
        }

        return $data;
    }
}
