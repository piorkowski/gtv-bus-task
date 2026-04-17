<?php

declare(strict_types=1);

namespace Example\Model;

use RestSDK\Model\ModelInterface;

final class Producers implements ModelInterface
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
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'],
            siteUrl: $data['site_url'] ?? null,
            logoFilename: $data['logo_filename'] ?? null,
            ordering: $data['ordering'] ?? null,
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
        ], fn($v) => $v !== null);
    }
}
