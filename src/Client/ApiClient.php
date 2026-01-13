<?php
declare(strict_types=1);

namespace RestSDK\Client;

use Psr\Log\LoggerInterface;
use RestSDK\Auth\BasicAuth;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class ApiClient implements ApiClientInterface
{
    public function __construct(
        private readonly ClientInterface $httpClient,
        private readonly RequestFactoryInterface $requestFactory,
        private readonly StreamFactoryInterface $streamFactory,
        private readonly string $baseUri,
        private readonly BasicAuth $auth,
        private readonly ?LoggerInterface $logger = null
    ) {}

    public function get(string $uri, array $queryParams = []): ResponseInterface
    {
        $url = $this->buildUrl($uri, $queryParams);
        $this->log('info', 'GET request', ['url' => $url, 'params' => $queryParams]);
        $request = $this->requestFactory->createRequest('GET', $url);
        $request = $this->auth->authorize($request);

        return $this->httpClient->sendRequest($request);
    }

    public function post(string $uri, array $body = []): ResponseInterface
    {
        return $this->sendRequestWithBody('POST', $uri, $body);
    }

    public function put(string $uri, array $body = []): ResponseInterface
    {
        return $this->sendRequestWithBody('PUT', $uri, $body);
    }

    public function patch(string $uri, array $body = []): ResponseInterface
    {
        return $this->sendRequestWithBody('PATCH', $uri, $body);
    }

    public function delete(string $uri): ResponseInterface
    {
        $request = $this->requestFactory->createRequest('DELETE', $this->baseUri . $uri);
        $request = $this->auth->authorize($request);

        return $this->httpClient->sendRequest($request);
    }

    private function sendRequestWithBody(string $method, string $uri, array $body): ResponseInterface
    {
        $request = $this->requestFactory->createRequest($method, $this->baseUri . $uri);
        $request = $this->auth->authorize($request);

        $stream = $this->streamFactory->createStream(json_encode($body));
        $request = $request->withBody($stream);

        return $this->httpClient->sendRequest($request);
    }

    private function buildUrl(string $uri, array $queryParams): string
    {
        $url = $this->baseUri . $uri;

        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }

        return $url;
    }

    private function log(string $level, string $message, array $context = []): void
    {
        $this->logger?->{$level}($message, $context);
    }
}