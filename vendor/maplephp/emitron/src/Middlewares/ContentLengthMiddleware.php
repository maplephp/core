<?php

declare(strict_types=1);

namespace MaplePHP\Emitron\Middlewares;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ContentLengthMiddleware implements MiddlewareInterface
{
    /**
     * Get the body content length reliably with PSR Stream.
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        $status = $response->getStatusCode();

        // If there is no body then no Content-Length
        if (in_array($status, [204, 304]) || $status < 200) {
            return $response;
        }

        $body = $response->getBody();
        if ($body->isSeekable()) {
            $body->seek(0, SEEK_END);
            $size = $body->tell();
            $body->rewind();
            $response = $response->withHeader('Content-Length', $size);
        }
        return $response;
    }
}
