<?php

declare(strict_types=1);

namespace RestSDK\Model;

interface ModelInterface
{
    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self;

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
