<?php

namespace RestSDK\Client;

use Psr\Http\Message\ResponseInterface;

interface ApiClientInterface
{
    public function get(string $uri, array $queryParams = []): ResponseInterface;
   public function post(string $uri, array $body = []): ResponseInterface;

    public function put(string $uri, array $body = []): ResponseInterface;

    public function delete(string $uri): ResponseInterface;

    public function patch(string $uri, array $body = []): ResponseInterface;
}