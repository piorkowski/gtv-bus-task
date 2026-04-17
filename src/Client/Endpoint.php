<?php

declare(strict_types=1);

namespace RestSDK\Client;

enum Endpoint: string
{
    case List = 'LIST';
    case Get = 'GET';
    case Create = 'CREATE';
    case Update = 'UPDATE';
    case Delete = 'DELETE';
}
