<?php

declare(strict_types=1);

namespace Tests\Client;

use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use RestSDK\Auth\BasicAuth;
use RestSDK\Client\ApiClient;

final class ApiClientTest extends TestCase
{
    private MockClient $httpClient;
    private ApiClient $apiClient;

    protected function setUp(): void
    {
        $this->httpClient = new MockClient();
        $factory = new Psr17Factory();

        $this->apiClient = new ApiClient(
            $this->httpClient,
            $factory,
            $factory,
            'https://api.example.com',
            new BasicAuth('user', 'pass'),
        );
    }

    public function testGetSetsContentTypeHeader(): void
    {
        $this->httpClient->addResponse(new Response(200));

        $this->apiClient->get('/test');

        $request = $this->httpClient->getLastRequest();
        self::assertNotNull($request);
        self::assertSame('application/json', $request->getHeaderLine('Content-Type'));
    }

    public function testPostSetsContentTypeHeader(): void
    {
        $this->httpClient->addResponse(new Response(200));

        $this->apiClient->post('/test', ['key' => 'value']);

        $request = $this->httpClient->getLastRequest();
        self::assertNotNull($request);
        self::assertSame('application/json', $request->getHeaderLine('Content-Type'));
    }

    public function testPutSetsContentTypeHeader(): void
    {
        $this->httpClient->addResponse(new Response(200));

        $this->apiClient->put('/test', ['key' => 'value']);

        $request = $this->httpClient->getLastRequest();
        self::assertNotNull($request);
        self::assertSame('application/json', $request->getHeaderLine('Content-Type'));
    }

    public function testDeleteSetsContentTypeHeader(): void
    {
        $this->httpClient->addResponse(new Response(200));

        $this->apiClient->delete('/test');

        $request = $this->httpClient->getLastRequest();
        self::assertNotNull($request);
        self::assertSame('application/json', $request->getHeaderLine('Content-Type'));
    }

    public function testGetSetsAuthorizationHeader(): void
    {
        $this->httpClient->addResponse(new Response(200));

        $this->apiClient->get('/test');

        $request = $this->httpClient->getLastRequest();
        self::assertNotNull($request);
        self::assertSame('Basic ' . base64_encode('user:pass'), $request->getHeaderLine('Authorization'));
    }

    public function testGetPrependsBaseUri(): void
    {
        $this->httpClient->addResponse(new Response(200));

        $this->apiClient->get('/test/path');

        $request = $this->httpClient->getLastRequest();
        self::assertNotNull($request);
        self::assertSame('https://api.example.com/test/path', (string) $request->getUri());
    }

    public function testGetAppendsQueryParams(): void
    {
        $this->httpClient->addResponse(new Response(200));

        $this->apiClient->get('/test', ['page' => 1, 'limit' => 10]);

        $request = $this->httpClient->getLastRequest();
        self::assertNotNull($request);
        $uri = (string) $request->getUri();
        self::assertStringContainsString('page=1', $uri);
        self::assertStringContainsString('limit=10', $uri);
    }

    public function testPostSendsJsonBody(): void
    {
        $this->httpClient->addResponse(new Response(200));

        $this->apiClient->post('/test', ['name' => 'Test']);

        $request = $this->httpClient->getLastRequest();
        self::assertNotNull($request);
        self::assertSame('{"name":"Test"}', (string) $request->getBody());
    }

    public function testPatchSendsJsonBody(): void
    {
        $this->httpClient->addResponse(new Response(200));

        $this->apiClient->patch('/test', ['name' => 'Updated']);

        $request = $this->httpClient->getLastRequest();
        self::assertNotNull($request);
        self::assertSame('PATCH', $request->getMethod());
        self::assertSame('{"name":"Updated"}', (string) $request->getBody());
    }
}
