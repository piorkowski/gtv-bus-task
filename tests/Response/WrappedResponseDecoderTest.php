<?php

declare(strict_types=1);

namespace Tests\Response;

use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use RestSDK\Exception\ApiException;
use RestSDK\Response\WrappedResponseDecoder;

final class WrappedResponseDecoderTest extends TestCase
{
    private WrappedResponseDecoder $decoder;

    protected function setUp(): void
    {
        $this->decoder = new WrappedResponseDecoder();
    }

    public function testUnwrapsSuccessEnvelope(): void
    {
        $response = new Response(200, [], json_encode([
            'success' => true,
            'data' => ['id' => 1, 'name' => 'Test'],
        ]));

        $data = $this->decoder->decode($response);

        self::assertSame(['id' => 1, 'name' => 'Test'], $data);
    }

    public function testReturnsEmptyArrayWhenDataKeyMissing(): void
    {
        $response = new Response(200, [], json_encode([
            'success' => true,
        ]));

        $data = $this->decoder->decode($response);

        self::assertSame([], $data);
    }

    public function testThrowsOnFailureWithErrorMessages(): void
    {
        $response = new Response(200, [], json_encode([
            'success' => false,
            'error' => ['messages' => ['Validation failed', 'Name is required']],
        ]));

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Validation failed, Name is required');

        $this->decoder->decode($response);
    }

    public function testThrowsOnFailureWithUnknownError(): void
    {
        $response = new Response(200, [], json_encode([
            'success' => false,
        ]));

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Unknown error');

        $this->decoder->decode($response);
    }

    public function testThrowsOnMissingSuccessKey(): void
    {
        $response = new Response(200, [], json_encode([
            'data' => ['id' => 1],
        ]));

        $this->expectException(ApiException::class);

        $this->decoder->decode($response);
    }
}
