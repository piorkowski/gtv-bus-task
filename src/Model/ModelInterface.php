<?php
declare(strict_types=1);

namespace RestSDK\Model;

interface ModelInterface
{
    public function getEndpoints(): array;
    public static function fromArray(array $data): self;
    public function toArray(): array;
}