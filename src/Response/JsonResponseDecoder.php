<?php

declare(strict_types=1);

namespace RestSDK\Response;

use Psr\Http\Message\ResponseInterface;
use RestSDK\Exception\ClientException;
use RestSDK\Exception\DeserializationException;
use RestSDK\Exception\ServerException;

final class JsonResponseDecoder implements ResponseDecoderInterface
{
    /**
     * @return array<string, mixed>
     */
    public function decode(ResponseInterface $response): array
    {
        $status = $response->getStatusCode();
        $body = (string) $response->getBody();

        if ($status >= 500) {
            throw new ServerException('Server error', $status, $body);
        }

        if ($status >= 400) {
            throw new ClientException('Client error', $status, $body);
        }

        if ($body === '') {
            return [];
        }

        try {
            $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new DeserializationException('Invalid JSON response: ' . $e->getMessage(), $status, $body, $e);
        }

        if (!is_array($data)) {
            throw new DeserializationException('Expected JSON object or array', $status, $body);
        }

        return $data;
    }
}
