<?php

declare(strict_types=1);

namespace RestSDK\Client;

use Psr\Http\Message\ResponseInterface;

interface ApiClientInterface
{
    /**
     * @param array<string, mixed> $queryParams
     */
    public function get(string $uri, array $queryParams = []): ResponseInterface;

    /**
     * @param array<string, mixed> $body
     */
    public function post(string $uri, array $body = []): ResponseInterface;

    /**
     * @param array<string, mixed> $body
     */
    public function put(string $uri, array $body = []): ResponseInterface;

    public function delete(string $uri): ResponseInterface;

    /**
     * @param array<string, mixed> $body
     */
    public function patch(string $uri, array $body = []): ResponseInterface;
}
