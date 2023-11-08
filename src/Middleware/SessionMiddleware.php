<?php

declare(strict_types=1);

namespace Application\Middleware;

use Application\Utils\Settings;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SessionMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (session_status() !== PHP_SESSION_NONE) {
            return $handler->handle($request);
        }

        session_start([
            'save_path' => Settings::getString('sessions.savePath'),
            'name' => Settings::getString('sessions.name'),
            'cookie_path' => Settings::getString('cookies.path'),
            'cookie_domain' => Settings::getString('cookies.domain'),
            'cookie_secure' => Settings::getBool('cookies.secure'),
            'cookie_httponly' => Settings::getBool('cookies.httpOnly'),
            'cookie_samesite' => Settings::getString('cookies.sameSite')
        ]);

        return $handler->handle($request);
    }
}
