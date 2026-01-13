<?php
declare(strict_types=1);

namespace Example\Api;

use Psr\Log\LoggerInterface;
use RestSDK\Model\ModelInterface;
use Example\Model\Producers;
use RestSDK\Client\AbstractApi;
use RestSDK\Client\ApiClientInterface;

final class ProducersApi extends AbstractApi
{
    public function __construct(ApiClientInterface $client, ?LoggerInterface $logger = null)
    {
        parent::__construct($client, Producers::class, $logger);
    }

    public function get(array $params = []): array
    {
        $endpoint = $this->getEndpoint('GET');
        $response = $this->client->get($endpoint, $params);
        $data = $this->decode($response);

        return array_map(
            fn(array $item) => Producers::fromArray($item),
            $data
        );
    }

    public function getById(int $id): Producers
    {
        $endpoint = $this->getEndpoint('GET', ['id' => $id]);
        $response = $this->client->get($endpoint);
        $data = $this->decode($response);

        return Producers::fromArray($data);
    }

    public function create(ModelInterface|Producers $model): Producers
    {
        $endpoint = $this->getEndpoint('POST');
        $response = $this->client->post($endpoint, $model->toArray());
        $data = $this->decode($response);

        return Producers::fromArray($data);
    }

    public function update(int $id, ModelInterface|Producers $model): Producers
    {
        $endpoint = $this->getEndpoint('PUT', ['id' => $id]);
        $response = $this->client->put($endpoint, $model->toArray());
        $data = $this->decode($response);

        return Producers::fromArray($data);
    }

    public function delete(int $id): bool
    {
        $endpoint = $this->getEndpoint('DELETE', ['id' => $id]);
        $response = $this->client->delete($endpoint);
        $this->decode($response);

        return true;
    }
}