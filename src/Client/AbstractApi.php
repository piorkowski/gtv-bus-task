<?php
declare(strict_types=1);

namespace RestSDK\Client;

use Psr\Log\LoggerInterface;
use RestSDK\Model\ModelInterface;
use Psr\Http\Message\ResponseInterface;
use RestSDK\Exception\ApiException;

abstract class AbstractApi
{
    public function __construct(
        protected readonly ApiClientInterface $client,
        protected readonly ModelInterface $modelClass,
        protected ?LoggerInterface $logger = null
    ) {}

    protected function getEndpoint(string $method, array $params = []): string
    {
        $endpoints = $this->modelClass::fromArray([])->getEndpoints();

        if (!isset($endpoints[$method])) {
            throw new ApiException("Method {$method} not supported for this resource");
        }

        $endpoint = $endpoints[$method];

        foreach ($params as $key => $value) {
            $endpoint = str_replace('{' . $key . '}', (string)$value, $endpoint);
        }

        return $endpoint;
    }

    public function get(array $params = []): array
    {
        throw new ApiException('GET method not implemented');
    }

    public function create(ModelInterface $model): ModelInterface
    {
        throw new ApiException('POST method not implemented');
    }

    public function update(int $id, ModelInterface $model): ModelInterface
    {
        throw new ApiException('PUT method not implemented');
    }

    public function delete(int $id): bool
    {
        throw new ApiException('DELETE method not implemented');
    }

    protected function decode(ResponseInterface $response): array
    {
        $status = $response->getStatusCode();

        if ($status >= 400 && $status < 500) {
            $this->logger?->warning('Client error response', ['status' => $status, 'body' => (string)$response->getBody()]);
        } elseif ($status >= 500) {
            $this->logger?->error('Server error response', ['status' => $status]);
        }

        $data = json_decode((string) $response->getBody(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ApiException('Invalid JSON response: ' . json_last_error_msg());
        }

        if (!isset($data['success']) || $data['success'] !== true) {
            $errorMessage = $data['error']['messages'] ?? 'Unknown error';
            $message = is_array($errorMessage) ? implode(', ', $errorMessage) : $errorMessage;

            if ($status >= 400 && $status < 500) {
                throw new ApiException($message, $status);
            }
            throw new ApiException($message, $status);
        }

        return $data['data'] ?? [];
    }
}