<?php

declare(strict_types=1);

namespace RestSDK\Client;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;
use RestSDK\Auth\AuthInterface;

final class ApiClient implements ApiClientInterface
{
    public function __construct(
        private readonly ClientInterface $httpClient,
        private readonly RequestFactoryInterface $requestFactory,
        private readonly StreamFactoryInterface $streamFactory,
        private readonly string $baseUri,
        private readonly AuthInterface $auth,
        private readonly ?LoggerInterface $logger = null,
    ) {}

    /**
     * @param array<string, mixed> $queryParams
     */
    public function get(string $uri, array $queryParams = []): ResponseInterface
    {
        $url = $this->buildUrl($uri, $queryParams);
        $this->log('info', 'GET request', ['url' => $url, 'params' => $queryParams]);
        $request = $this->requestFactory->createRequest('GET', $url);
        $request = $this->auth->authorize($request);

        return $this->httpClient->sendRequest($request);
    }

    /**
     * @param array<string, mixed> $body
     */
    public function post(string $uri, array $body = []): ResponseInterface
    {
        return $this->sendRequestWithBody('POST', $uri, $body);
    }

    /**
     * @param array<string, mixed> $body
     */
    public function put(string $uri, array $body = []): ResponseInterface
    {
        return $this->sendRequestWithBody('PUT', $uri, $body);
    }

    /**
     * @param array<string, mixed> $body
     */
    public function patch(string $uri, array $body = []): ResponseInterface
    {
        return $this->sendRequestWithBody('PATCH', $uri, $body);
    }

    public function delete(string $uri): ResponseInterface
    {
        $url = $this->baseUri . $uri;
        $this->log('info', 'DELETE request', ['url' => $url]);
        $request = $this->requestFactory->createRequest('DELETE', $url);
        $request = $this->auth->authorize($request);

        return $this->httpClient->sendRequest($request);
    }

    /**
     * @param array<string, mixed> $body
     */
    private function sendRequestWithBody(string $method, string $uri, array $body): ResponseInterface
    {
        $url = $this->baseUri . $uri;
        $this->log('info', $method . ' request', ['url' => $url, 'body' => $body]);
        $request = $this->requestFactory->createRequest($method, $url);
        $request = $this->auth->authorize($request);

        $stream = $this->streamFactory->createStream(json_encode($body, JSON_THROW_ON_ERROR));
        $request = $request->withBody($stream);

        return $this->httpClient->sendRequest($request);
    }

    /**
     * @param array<string, mixed> $queryParams
     */
    private function buildUrl(string $uri, array $queryParams): string
    {
        $url = $this->baseUri . $uri;

        if ($queryParams !== []) {
            $url .= '?' . http_build_query($queryParams);
        }

        return $url;
    }

    /**
     * @param array<string, mixed> $context
     */
    private function log(string $level, string $message, array $context = []): void
    {
        $this->logger?->{$level}($message, $context);
    }
}
