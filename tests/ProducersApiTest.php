<?php

declare(strict_types=1);

namespace Tests;

use Example\Api\ProducersApi;
use Example\Model\Producer;
use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use RestSDK\Auth\BasicAuth;
use RestSDK\Client\ApiClient;
use RestSDK\Exception\ApiException;
use RestSDK\Response\WrappedResponseDecoder;

final class ProducersApiTest extends TestCase
{
    private MockClient $httpClient;
    private ProducersApi $api;

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

        $this->api = new ProducersApi($apiClient, new WrappedResponseDecoder());
    }

    public function testListReturnsArrayOfProducers(): void
    {
        $this->httpClient->addResponse(new Response(
            200,
            [],
            json_encode([
                'success' => true,
                'data' => [
                    ['id' => 1, 'name' => 'Producer A'],
                    ['id' => 2, 'name' => 'Producer B'],
                ],
            ]),
        ));

        $producers = $this->api->list();

        self::assertCount(2, $producers);
        self::assertInstanceOf(Producer::class, $producers[0]);
        self::assertSame('Producer A', $producers[0]->name);
        self::assertSame(1, $producers[0]->id);
        self::assertSame('Producer B', $producers[1]->name);
    }

    public function testListWithQueryParams(): void
    {
        $this->httpClient->addResponse(new Response(
            200,
            [],
            json_encode([
                'success' => true,
                'data' => [
                    ['id' => 1, 'name' => 'Producer A'],
                ],
            ]),
        ));

        $producers = $this->api->list(['page' => 1, 'limit' => 10]);

        self::assertCount(1, $producers);

        $lastRequest = $this->httpClient->getLastRequest();
        self::assertNotNull($lastRequest);
        self::assertStringContainsString('page=1', (string) $lastRequest->getUri());
        self::assertStringContainsString('limit=10', (string) $lastRequest->getUri());
    }

    public function testListReturnsEmptyArrayWhenNoData(): void
    {
        $this->httpClient->addResponse(new Response(
            200,
            [],
            json_encode(['success' => true, 'data' => []]),
        ));

        $producers = $this->api->list();

        self::assertCount(0, $producers);
    }

    public function testCreate(): void
    {
        $this->httpClient->addResponse(new Response(
            201,
            [],
            json_encode([
                'success' => true,
                'data' => [
                    'id' => 123,
                    'name' => 'New Producer',
                    'site_url' => 'https://example.com',
                ],
            ]),
        ));

        $model = new Producer(null, 'New Producer', 'https://example.com');
        $created = $this->api->create($model);

        self::assertSame(123, $created->id);
        self::assertSame('New Producer', $created->name);
        self::assertSame('https://example.com', $created->siteUrl);

        $lastRequest = $this->httpClient->getLastRequest();
        self::assertNotNull($lastRequest);
        self::assertSame('POST', $lastRequest->getMethod());
    }

    public function testCreateSendsCorrectBody(): void
    {
        $this->httpClient->addResponse(new Response(
            201,
            [],
            json_encode([
                'success' => true,
                'data' => ['id' => 1, 'name' => 'Test', 'ordering' => 5],
            ]),
        ));

        $model = new Producer(null, 'Test', ordering: 5);
        $this->api->create($model);

        $lastRequest = $this->httpClient->getLastRequest();
        self::assertNotNull($lastRequest);

        $body = json_decode((string) $lastRequest->getBody(), true);
        self::assertSame('Test', $body['name']);
        self::assertSame(5, $body['ordering']);
        self::assertArrayNotHasKey('site_url', $body);
    }

    public function testGetById(): void
    {
        $this->httpClient->addResponse(new Response(
            200,
            [],
            json_encode([
                'success' => true,
                'data' => ['id' => 42, 'name' => 'Single Producer'],
            ]),
        ));

        $producer = $this->api->get(42);

        self::assertSame(42, $producer->id);
        self::assertSame('Single Producer', $producer->name);

        $lastRequest = $this->httpClient->getLastRequest();
        self::assertNotNull($lastRequest);
        self::assertStringContainsString('/producers/42', (string) $lastRequest->getUri());
    }

    public function testUpdate(): void
    {
        $this->httpClient->addResponse(new Response(
            200,
            [],
            json_encode([
                'success' => true,
                'data' => ['id' => 1, 'name' => 'Updated'],
            ]),
        ));

        $model = new Producer(null, 'Updated');
        $updated = $this->api->update(1, $model);

        self::assertSame('Updated', $updated->name);

        $lastRequest = $this->httpClient->getLastRequest();
        self::assertNotNull($lastRequest);
        self::assertSame('PUT', $lastRequest->getMethod());
        self::assertStringContainsString('/producers/1', (string) $lastRequest->getUri());
    }

    public function testDelete(): void
    {
        $this->httpClient->addResponse(new Response(
            200,
            [],
            json_encode(['success' => true]),
        ));

        $this->api->delete(5);

        $lastRequest = $this->httpClient->getLastRequest();
        self::assertNotNull($lastRequest);
        self::assertSame('DELETE', $lastRequest->getMethod());
        self::assertStringContainsString('/producers/5', (string) $lastRequest->getUri());
    }

    public function testApiErrorThrowsException(): void
    {
        $this->httpClient->addResponse(new Response(
            400,
            [],
            json_encode([
                'success' => false,
                'error' => ['messages' => ['Validation failed']],
            ]),
        ));

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Client error');

        $this->api->list();
    }

    public function testInvalidJsonThrowsException(): void
    {
        $this->httpClient->addResponse(new Response(
            200,
            [],
            'not json',
        ));

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Invalid JSON response');

        $this->api->list();
    }

    public function testRequestContainsAuthorizationHeader(): void
    {
        $this->httpClient->addResponse(new Response(
            200,
            [],
            json_encode(['success' => true, 'data' => []]),
        ));

        $this->api->list();

        $lastRequest = $this->httpClient->getLastRequest();
        self::assertNotNull($lastRequest);

        $authHeader = $lastRequest->getHeaderLine('Authorization');
        self::assertStringStartsWith('Basic ', $authHeader);
        self::assertSame('Basic ' . base64_encode('user:pass'), $authHeader);
    }

    public function testRequestContainsJsonContentType(): void
    {
        $this->httpClient->addResponse(new Response(
            200,
            [],
            json_encode(['success' => true, 'data' => []]),
        ));

        $this->api->list();

        $lastRequest = $this->httpClient->getLastRequest();
        self::assertNotNull($lastRequest);
        self::assertSame('application/json', $lastRequest->getHeaderLine('Content-Type'));
    }
}
