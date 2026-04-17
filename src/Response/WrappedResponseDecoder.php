<?php

declare(strict_types=1);

namespace RestSDK\Response;

use Psr\Http\Message\ResponseInterface;
use RestSDK\Exception\ApiException;

final class WrappedResponseDecoder implements ResponseDecoderInterface
{
    public function __construct(
        private readonly ResponseDecoderInterface $inner = new JsonResponseDecoder(),
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function decode(ResponseInterface $response): array
    {
        $data = $this->inner->decode($response);

        if (!isset($data['success']) || $data['success'] !== true) {
            $errorMessage = $data['error']['messages'] ?? 'Unknown error';
            $message = is_array($errorMessage) ? implode(', ', $errorMessage) : (string) $errorMessage;

            throw new ApiException($message, $response->getStatusCode(), (string) $response->getBody());
        }

        /** @var array<string, mixed> */
        return $data['data'] ?? [];
    }
}
