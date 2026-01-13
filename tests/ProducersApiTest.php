<?php
declare(strict_types=1);

use Example\Api\ProducersApi;
use Example\Model\Producers;
use Nyholm\Psr7\Response;
use RestSDK\Client\ApiClient;
use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

class ProducersApiTest extends TestCase
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
            new BasicAuth('user', 'pass')
        );

        $this->api = new ProducersApi($apiClient);
    }

    public function testGetAll(): void
    {
        $response = new Response(
            200,
            [],
            json_encode([
                'success' => true,
                'data' => [
                    ['id' => 1, 'name' => 'Producer A'],
                    ['id' => 2, 'name' => 'Producer B'],
                ]
            ])
        );
        $this->httpClient->addResponse($response);

        $producers = $this->api->get();

        $this->assertCount(2, $producers);
        $this->assertInstanceOf(Producers::class, $producers[0]);
        $this->assertSame('Producer A', $producers[0]->name);
    }

    public function testCreate(): void
    {
        $response = new Response(
            201,
            [],
            json_encode([
                'success' => true,
                'data' => ['id' => 123, 'name' => 'New Producer', 'site_url' => 'https://example.com']
            ])
        );
        $this->httpClient->addResponse($response);

        $model = new Producers(null, 'New Producer', 'https://example.com', null, null, null);
        $created = $this->api->create($model);

        $this->assertSame(123, $created->id);
        $this->assertSame('New Producer', $created->name);
    }
}