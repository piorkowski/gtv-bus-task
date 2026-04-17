<?php

declare(strict_types=1);

namespace RestSDK\Response;

use Psr\Http\Message\ResponseInterface;
use RestSDK\Exception\ApiException;

interface ResponseDecoderInterface
{
    /**
     * @return array<string, mixed>
     *
     * @throws ApiException
     */
    public function decode(ResponseInterface $response): array;
}
