<?php

declare(strict_types=1);

namespace Tests\Client;

use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use RestSDK\Auth\BasicAuth;
use RestSDK\Client\ApiClient;
use RestSDK\Client\CrudApi;
use RestSDK\Client\Endpoint;
use RestSDK\Exception\ApiException;
use RestSDK\Model\ModelInterface;
use RestSDK\Response\JsonResponseDecoder;

final class CrudApiTest extends TestCase
{
    private MockClient $httpClient;
    private CrudApi $api;

    protected function setUp(): void
    {
        $this->httpClient = new MockClient();
        $factory = new Psr17Factory();

        $apiClient = new ApiClient(
            $this->httpClient,
            $factory,
            $factory,
            'https://api.example.com',
            new BasicAuth('user', 'pass'),
        );

        $this->api = new class ($apiClient, new JsonResponseDecoder()) extends CrudApi {
            protected function getModelClass(): string
            {
                return FakeModel::class;
            }

            protected function getEndpoints(): array
            {
                return [
                    Endpoint::List->value => '/items',
                    Endpoint::Get->value => '/items/{id}',
                    Endpoint::Create->value => '/items',
                    Endpoint::Update->value => '/items/{id}',
                    Endpoint::Delete->value => '/items/{id}',
                ];
            }
        };
    }

    public function testListReturnsMappedModels(): void
    {
        $this->httpClient->addResponse(new Response(
            200,
            [],
            json_encode([
                ['id' => 1, 'name' => 'Item A'],
                ['id' => 2, 'name' => 'Item B'],
            ]),
        ));

        $items = $this->api->list();

        self::assertCount(2, $items);
        self::assertInstanceOf(FakeModel::class, $items[0]);
        self::assertSame(1, $items[0]->id);
        self::assertSame('Item B', $items[1]->name);
    }

    public function testGetReturnsModel(): void
    {
        $this->httpClient->addResponse(new Response(
            200,
            [],
            json_encode(['id' => 42, 'name' => 'Single']),
        ));

        $item = $this->api->get(42);

        self::assertInstanceOf(FakeModel::class, $item);
        self::assertSame(42, $item->id);
        self::assertSame('Single', $item->name);

        $request = $this->httpClient->getLastRequest();
        self::assertNotNull($request);
        self::assertStringContainsString('/items/42', (string) $request->getUri());
    }

    public function testCreateSendsPostAndReturnsModel(): void
    {
        $this->httpClient->addResponse(new Response(
            201,
            [],
            json_encode(['id' => 99, 'name' => 'Created']),
        ));

        $model = new FakeModel(null, 'Created');
        $result = $this->api->create($model);

        self::assertSame(99, $result->id);

        $request = $this->httpClient->getLastRequest();
        self::assertNotNull($request);
        self::assertSame('POST', $request->getMethod());
    }

    public function testUpdateSendsPutAndReturnsModel(): void
    {
        $this->httpClient->addResponse(new Response(
            200,
            [],
            json_encode(['id' => 1, 'name' => 'Updated']),
        ));

        $model = new FakeModel(null, 'Updated');
        $result = $this->api->update(1, $model);

        self::assertSame('Updated', $result->name);

        $request = $this->httpClient->getLastRequest();
        self::assertNotNull($request);
        self::assertSame('PUT', $request->getMethod());
        self::assertStringContainsString('/items/1', (string) $request->getUri());
    }

    public function testDeleteSendsDeleteRequest(): void
    {
        $this->httpClient->addResponse(new Response(
            200,
            [],
            json_encode([]),
        ));

        $this->api->delete(5);

        $request = $this->httpClient->getLastRequest();
        self::assertNotNull($request);
        self::assertSame('DELETE', $request->getMethod());
        self::assertStringContainsString('/items/5', (string) $request->getUri());
    }

    public function testUnsupportedEndpointThrowsException(): void
    {
        $api = new class (
            $this->api,
            new JsonResponseDecoder(),
        ) extends CrudApi {
            /** @phpstan-ignore constructor.unusedParameter */
            public function __construct(
                private readonly CrudApi $parent,
                \RestSDK\Response\ResponseDecoderInterface $decoder,
            ) {
                parent::__construct($this->parent->client, $decoder);
            }

            protected function getModelClass(): string
            {
                return FakeModel::class;
            }

            protected function getEndpoints(): array
            {
                return [];
            }
        };

        $this->expectException(ApiException::class);
        $api->list();
    }
}

/**
 * @implements ModelInterface
 */
final class FakeModel implements ModelInterface
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $name = '',
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'] ?? '',
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'name' => $this->name,
        ], static fn(mixed $v): bool => $v !== null);
    }
}
