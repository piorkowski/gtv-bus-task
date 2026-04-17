<?php

declare(strict_types=1);

namespace RestSDK\Auth;

use Psr\Http\Message\RequestInterface;

interface AuthInterface
{
    public function authorize(RequestInterface $request): RequestInterface;
}
