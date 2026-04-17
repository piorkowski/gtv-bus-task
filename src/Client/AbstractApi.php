<?php

declare(strict_types=1);

namespace RestSDK\Client;

use Psr\Http\Message\ResponseInterface;
use RestSDK\Exception\ApiException;
use RestSDK\Response\ResponseDecoderInterface;

abstract class AbstractApi
{
    public function __construct(
        protected readonly ApiClientInterface $client,
        protected readonly ResponseDecoderInterface $decoder,
    ) {}

    /**
     * @return array<string, string> Endpoint case value => URI path mapping
     */
    abstract protected function getEndpoints(): array;

    /**
     * @param array<string, int|string> $params
     */
    protected function resolveEndpoint(string $method, array $params = []): string
    {
        $endpoints = $this->getEndpoints();

        if (!isset($endpoints[$method])) {
            throw new ApiException("Method {$method} not supported for this resource");
        }

        $endpoint = $endpoints[$method];

        foreach ($params as $key => $value) {
            $endpoint = str_replace('{' . $key . '}', (string) $value, $endpoint);
        }

        return $endpoint;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ApiException
     */
    protected function decode(ResponseInterface $response): array
    {
        return $this->decoder->decode($response);
    }
}
