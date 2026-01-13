<?php
declare(strict_types=1);

namespace RestSDK\Auth;

use Psr\Http\Message\RequestInterface;

final class BasicAuth
{
    public function __construct(
        private string $username,
        private string $password
    ) {
//      This is shit. It should be #[Autowire] here, but this is a framework-agnostic approach, so I decided not to use additional libraries just for this.
        $this->username = $_ENV['API_USER_LOGIN'];
        $this->password = $_ENV['API_USER_PASSWORD'];
    }

    public function authorize(RequestInterface $request): RequestInterface
    {
        return $request->withHeader(
            'Authorization',
            'Basic ' . base64_encode($this->username . ':' . $this->password)
        )->withHeader('Content-Type', 'application/json');
    }
}
