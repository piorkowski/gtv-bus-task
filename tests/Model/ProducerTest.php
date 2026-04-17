<?php

declare(strict_types=1);

namespace Tests\Model;

use Example\Model\Producer;
use PHPUnit\Framework\TestCase;
use RestSDK\Exception\DeserializationException;
use RestSDK\Model\ModelInterface;

final class ProducerTest extends TestCase
{
    public function testImplementsModelInterface(): void
    {
        $producer = new Producer(1, 'Test');

        self::assertInstanceOf(ModelInterface::class, $producer);
    }

    public function testFromArrayWithAllFields(): void
    {
        $data = [
            'id' => 1,
            'name' => 'Test Producer',
            'site_url' => 'https://example.com',
            'logo_filename' => 'logo.png',
            'ordering' => 5,
            'source_id' => 'ext-123',
        ];

        $producer = Producer::fromArray($data);

        self::assertSame(1, $producer->id);
        self::assertSame('Test Producer', $producer->name);
        self::assertSame('https://example.com', $producer->siteUrl);
        self::assertSame('logo.png', $producer->logoFilename);
        self::assertSame(5, $producer->ordering);
        self::assertSame('ext-123', $producer->sourceId);
    }

    public function testFromArrayWithMinimalFields(): void
    {
        $producer = Producer::fromArray(['name' => 'Minimal']);

        self::assertNull($producer->id);
        self::assertSame('Minimal', $producer->name);
        self::assertNull($producer->siteUrl);
        self::assertNull($producer->logoFilename);
        self::assertNull($producer->ordering);
        self::assertNull($producer->sourceId);
    }

    public function testFromArrayThrowsOnMissingName(): void
    {
        $this->expectException(DeserializationException::class);
        $this->expectExceptionMessage('Missing required field: name');

        Producer::fromArray(['id' => 1]);
    }

    public function testFromArrayThrowsOnNonStringName(): void
    {
        $this->expectException(DeserializationException::class);
        $this->expectExceptionMessage('Missing required field: name');

        Producer::fromArray(['name' => 123]);
    }

    public function testToArrayFiltersNullValues(): void
    {
        $producer = new Producer(null, 'Test', ordering: 5);

        $array = $producer->toArray();

        self::assertSame(['name' => 'Test', 'ordering' => 5], $array);
        self::assertArrayNotHasKey('site_url', $array);
        self::assertArrayNotHasKey('logo_filename', $array);
        self::assertArrayNotHasKey('source_id', $array);
    }

    public function testToArrayIncludesAllNonNullValues(): void
    {
        $producer = new Producer(1, 'Full', 'https://example.com', 'logo.png', 10, 'src-1');

        $array = $producer->toArray();

        self::assertSame('Full', $array['name']);
        self::assertSame('https://example.com', $array['site_url']);
        self::assertSame('logo.png', $array['logo_filename']);
        self::assertSame(10, $array['ordering']);
        self::assertSame('src-1', $array['source_id']);
    }
}
