<?php

declare(strict_types=1);

namespace MaplePHP\Emitron\Middlewares;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CacheControlMiddleware implements MiddlewareInterface
{
    /**
     * Set cache control if it does not exist
     *
     * Note: Clearing cache on dynamic content is a good standard to make sure that no
     * sensitive content will be cached.
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        if (!$response->hasHeader("Cache-Control")) {
            $response = $response->withHeaders([
                "Cache-Control" => "no-store, no-cache, must-revalidate, private",
                "Expires" => "Sat, 26 Jul 1997 05:00:00 GMT"
            ]);
        }
        return $response;
    }
}
