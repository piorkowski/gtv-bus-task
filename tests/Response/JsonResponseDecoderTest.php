<?php

declare(strict_types=1);

namespace Tests\Response;

use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use RestSDK\Exception\ClientException;
use RestSDK\Exception\DeserializationException;
use RestSDK\Exception\ServerException;
use RestSDK\Response\JsonResponseDecoder;

final class JsonResponseDecoderTest extends TestCase
{
    private JsonResponseDecoder $decoder;

    protected function setUp(): void
    {
        $this->decoder = new JsonResponseDecoder();
    }

    public function testDecodesValidJson(): void
    {
        $response = new Response(200, [], json_encode(['key' => 'value']));

        $data = $this->decoder->decode($response);

        self::assertSame(['key' => 'value'], $data);
    }

    public function testDecodesJsonArray(): void
    {
        $response = new Response(200, [], json_encode([['id' => 1], ['id' => 2]]));

        $data = $this->decoder->decode($response);

        self::assertCount(2, $data);
    }

    public function testReturnsEmptyArrayForEmptyBody(): void
    {
        $response = new Response(204, [], '');

        $data = $this->decoder->decode($response);

        self::assertSame([], $data);
    }

    public function testThrowsClientExceptionOn4xx(): void
    {
        $response = new Response(422, [], json_encode(['error' => 'Validation failed']));

        $this->expectException(ClientException::class);
        $this->decoder->decode($response);
    }

    public function testThrowsServerExceptionOn5xx(): void
    {
        $response = new Response(500, [], 'Internal Server Error');

        $this->expectException(ServerException::class);
        $this->decoder->decode($response);
    }

    public function testThrowsDeserializationExceptionOnInvalidJson(): void
    {
        $response = new Response(200, [], 'not json');

        $this->expectException(DeserializationException::class);
        $this->expectExceptionMessage('Invalid JSON response');
        $this->decoder->decode($response);
    }

    public function testClientExceptionContainsStatusCodeAndBody(): void
    {
        $body = json_encode(['error' => 'Not found']);
        $response = new Response(404, [], $body);

        try {
            $this->decoder->decode($response);
            self::fail('Expected ClientException');
        } catch (ClientException $e) {
            self::assertSame(404, $e->getStatusCode());
            self::assertSame($body, $e->getResponseBody());
        }
    }
}
