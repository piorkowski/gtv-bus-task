<?php

declare(strict_types=1);

namespace Example\Api;

use Example\Model\Producer;
use RestSDK\Client\CrudApi;
use RestSDK\Client\Endpoint;

/**
 * @extends CrudApi<Producer>
 */
final class ProducersApi extends CrudApi
{
    protected function getModelClass(): string
    {
        return Producer::class;
    }

    protected function getEndpoints(): array
    {
        return [
            Endpoint::List->value => '/shop_api/v1/producers',
            Endpoint::Get->value => '/shop_api/v1/producers/{id}',
            Endpoint::Create->value => '/shop_api/v1/producers',
            Endpoint::Update->value => '/shop_api/v1/producers/{id}',
            Endpoint::Delete->value => '/shop_api/v1/producers/{id}',
        ];
    }
}
