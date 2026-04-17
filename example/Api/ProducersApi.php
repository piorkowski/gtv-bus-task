<?php

declare(strict_types=1);

namespace Example\Api;

use Example\Model\Producers;
use RestSDK\Client\AbstractApi;
use RestSDK\Model\ModelInterface;

final class ProducersApi extends AbstractApi
{
    protected function getEndpoints(): array
    {
        return [
            'GET' => '/shop_api/v1/producers',
            'GET_ONE' => '/shop_api/v1/producers/{id}',
            'POST' => '/shop_api/v1/producers',
            'PUT' => '/shop_api/v1/producers/{id}',
            'DELETE' => '/shop_api/v1/producers/{id}',
        ];
    }

    /**
     * @param array<string, mixed> $params
     *
     * @return list<Producers>
     */
    public function get(array $params = []): array
    {
        $endpoint = $this->resolveEndpoint('GET');
        $response = $this->client->get($endpoint, $params);
        $data = $this->decode($response);

        return array_values(array_map(
            fn(array $item) => Producers::fromArray($item),
            $data,
        ));
    }

    public function getById(int $id): Producers
    {
        $endpoint = $this->resolveEndpoint('GET_ONE', ['id' => $id]);
        $response = $this->client->get($endpoint);
        $data = $this->decode($response);

        return Producers::fromArray($data);
    }

    public function create(ModelInterface|Producers $model): Producers
    {
        $endpoint = $this->resolveEndpoint('POST');
        $response = $this->client->post($endpoint, $model->toArray());
        $data = $this->decode($response);

        return Producers::fromArray($data);
    }

    public function update(int $id, ModelInterface|Producers $model): Producers
    {
        $endpoint = $this->resolveEndpoint('PUT', ['id' => $id]);
        $response = $this->client->put($endpoint, $model->toArray());
        $data = $this->decode($response);

        return Producers::fromArray($data);
    }

    public function delete(int $id): bool
    {
        $endpoint = $this->resolveEndpoint('DELETE', ['id' => $id]);
        $response = $this->client->delete($endpoint);
        $this->decode($response);

        return true;
    }
}
