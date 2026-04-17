<?php

declare(strict_types=1);

namespace Example\Model;

use RestSDK\Exception\DeserializationException;
use RestSDK\Model\ModelInterface;

final class Producer implements ModelInterface
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $name,
        public readonly ?string $siteUrl = null,
        public readonly ?string $logoFilename = null,
        public readonly ?int $ordering = null,
        public readonly ?string $sourceId = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        if (!isset($data['name']) || !is_string($data['name'])) {
            throw new DeserializationException('Missing required field: name');
        }

        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            name: $data['name'],
            siteUrl: $data['site_url'] ?? null,
            logoFilename: $data['logo_filename'] ?? null,
            ordering: isset($data['ordering']) ? (int) $data['ordering'] : null,
            sourceId: $data['source_id'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'site_url' => $this->siteUrl,
            'logo_filename' => $this->logoFilename,
            'ordering' => $this->ordering,
            'source_id' => $this->sourceId,
        ], static fn(mixed $v): bool => $v !== null);
    }
}
