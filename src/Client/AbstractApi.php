<?php

declare(strict_types=1);

namespace RestSDK\Client;

use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use RestSDK\Exception\ApiException;

abstract class AbstractApi
{
    public function __construct(
        protected readonly ApiClientInterface $client,
        protected readonly ?LoggerInterface $logger = null,
    ) {}

    /**
     * @return array<string, string> HTTP method => endpoint path mapping
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
        $status = $response->getStatusCode();

        if ($status >= 400 && $status < 500) {
            $this->logger?->warning('Client error response', [
                'status' => $status,
                'body' => (string) $response->getBody(),
            ]);
        } elseif ($status >= 500) {
            $this->logger?->error('Server error response', ['status' => $status]);
        }

        $body = (string) $response->getBody();

        /** @var array<string, mixed>|null $data */
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ApiException('Invalid JSON response: ' . json_last_error_msg());
        }

        if (!is_array($data) || !isset($data['success']) || $data['success'] !== true) {
            $errorMessage = $data['error']['messages'] ?? 'Unknown error';
            $message = is_array($errorMessage) ? implode(', ', $errorMessage) : (string) $errorMessage;

            throw new ApiException($message, $status);
        }

        /** @var array<string, mixed> */
        return $data['data'] ?? [];
    }
}
