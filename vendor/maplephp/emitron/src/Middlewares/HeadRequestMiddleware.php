<?php

declare(strict_types=1);

namespace MaplePHP\Emitron\Middlewares;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HeadRequestMiddleware implements MiddlewareInterface
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

        if (strtoupper($request->getMethod()) === 'HEAD') {
            $body = $response->getBody();
            if ($body->isWritable() && $body->isSeekable()) {
                $body->rewind();
                $body->write('');
            }
        }

        return $response;
    }
}
