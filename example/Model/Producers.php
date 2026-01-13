<?php
declare(strict_types=1);

namespace Example\Model;

use RestSDK\Model\ModelInterface;

class Producers implements ModelInterface
{
    private const array ENDPOINTS = [
        'GET' => '/shop_api/v1/producers',
        'POST' => '/shop_api/v1/producers'
    ];

    public function __construct(
        public readonly ?int $id,
        public readonly string $name,
        public readonly ?string $siteUrl,
        public readonly ?string $logoFilename,
        public readonly ?int $ordering,
        public readonly ?string $sourceId
    ) {}

    public function getEndpoints(): array
    {
        return self::ENDPOINTS;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'],
            siteUrl: $data['site_url'] ?? null,
            logoFilename: $data['logo_filename'] ?? null,
            ordering: $data['ordering'] ?? null,
            sourceId: $data['source_id'] ?? null
        );
    }
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'site_url' => $this->siteUrl,
            'logo_filename' => $this->logoFilename,
            'ordering' => $this->ordering,
            'source_id' => $this->sourceId,
        ], fn ($v) => $v !== null);
    }
}