<?php

declare(strict_types=1);

namespace RestSDK\Client;

use RestSDK\Model\ModelInterface;

/**
 * @template T of ModelInterface
 */
abstract class CrudApi extends AbstractApi
{
    /**
     * @return class-string<T>
     */
    abstract protected function getModelClass(): string;

    /**
     * @param array<string, mixed> $params
     *
     * @return list<T>
     */
    public function list(array $params = []): array
    {
        $endpoint = $this->resolveEndpoint(Endpoint::List->value);
        $response = $this->client->get($endpoint, $params);
        $items = $this->decode($response);
        $class = $this->getModelClass();

        /** @var list<T> */
        return array_values(array_map(
            static fn(array $item): ModelInterface => $class::fromArray($item),
            $items,
        ));
    }

    /**
     * @return T
     */
    public function get(int|string $id): ModelInterface
    {
        $endpoint = $this->resolveEndpoint(Endpoint::Get->value, ['id' => $id]);
        $response = $this->client->get($endpoint);
        $class = $this->getModelClass();

        /** @var T */
        return $class::fromArray($this->decode($response));
    }

    /**
     * @param T $model
     *
     * @return T
     */
    public function create(ModelInterface $model): ModelInterface
    {
        $endpoint = $this->resolveEndpoint(Endpoint::Create->value);
        $response = $this->client->post($endpoint, $model->toArray());
        $class = $this->getModelClass();

        /** @var T */
        return $class::fromArray($this->decode($response));
    }

    /**
     * @param T $model
     *
     * @return T
     */
    public function update(int|string $id, ModelInterface $model): ModelInterface
    {
        $endpoint = $this->resolveEndpoint(Endpoint::Update->value, ['id' => $id]);
        $response = $this->client->put($endpoint, $model->toArray());
        $class = $this->getModelClass();

        /** @var T */
        return $class::fromArray($this->decode($response));
    }

    public function delete(int|string $id): void
    {
        $endpoint = $this->resolveEndpoint(Endpoint::Delete->value, ['id' => $id]);
        $response = $this->client->delete($endpoint);
        $this->decode($response);
    }
}
