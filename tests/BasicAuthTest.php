<?php

declare(strict_types=1);

namespace Tests;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use RestSDK\Auth\AuthInterface;
use RestSDK\Auth\BasicAuth;

final class BasicAuthTest extends TestCase
{
    public function testImplementsAuthInterface(): void
    {
        $auth = new BasicAuth('user', 'pass');

        self::assertInstanceOf(AuthInterface::class, $auth);
    }

    public function testAuthorizeSetsBasicAuthHeader(): void
    {
        $auth = new BasicAuth('rest', 'vKTUeyrt1!');
        $factory = new Psr17Factory();
        $request = $factory->createRequest('GET', 'https://example.com');

        $authorized = $auth->authorize($request);

        self::assertSame(
            'Basic ' . base64_encode('rest:vKTUeyrt1!'),
            $authorized->getHeaderLine('Authorization'),
        );
    }

    public function testAuthorizeSetsContentTypeHeader(): void
    {
        $auth = new BasicAuth('user', 'pass');
        $factory = new Psr17Factory();
        $request = $factory->createRequest('GET', 'https://example.com');

        $authorized = $auth->authorize($request);

        self::assertSame('application/json', $authorized->getHeaderLine('Content-Type'));
    }

    public function testAuthorizeDoesNotMutateOriginalRequest(): void
    {
        $auth = new BasicAuth('user', 'pass');
        $factory = new Psr17Factory();
        $request = $factory->createRequest('GET', 'https://example.com');

        $auth->authorize($request);

        self::assertSame('', $request->getHeaderLine('Authorization'));
    }
}
