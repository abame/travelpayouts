<?php

namespace TravelPayouts\Components;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use RuntimeException;

class Client extends BaseClient
{
    private const API_HOST = 'https://api.travelpayouts.com';

    private string $token;

    public function __construct(string $token)
    {
        $this->token = $token;

        $this->client = new HttpClient(
            [
                'base_uri' => self::API_HOST,
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
        $url = '/' . $this->getApiVersion() . '/' . $url;
        $params = [
            'http_errors' => false,
        ];

        if ($replaceOptions) {
            $paramName = $type === 'GET' ? 'query' : 'body';
            $params[$paramName] = $options;
        }
        if (!$replaceOptions) {
            $params += $options;
        }

        $body = $this->validateResponse($url, $params, $type);

        return $this->makeApiResponse($body->getContents());
    }

    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @return mixed
     * @throws RuntimeException
     */
    private function makeApiResponse(string $jsonString)
    {
        $data = json_decode($jsonString, true);
        if (!$data) {
            throw new RuntimeException(sprintf('Unable to decode json response: %s', $jsonString));
        }

        return $data;
    }
}
