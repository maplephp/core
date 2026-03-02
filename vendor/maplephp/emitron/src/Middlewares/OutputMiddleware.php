<?php

declare(strict_types=1);

namespace MaplePHP\Emitron\Middlewares;

use MaplePHP\Http\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class OutputMiddleware implements MiddlewareInterface
{
    private ?StreamInterface $stream = null;

    public function __construct(?StreamInterface $stream = null)
    {
        $this->stream = $stream;
    }
    /**
     * Get the body content length reliably with PSR Stream.
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {

        if ($this->stream === null) {
            $this->stream = new Stream(Stream::TEMP);
        }

        $response = $handler->handle($request);
        $response = $response->withBody($this->stream);
        return $response;
    }


    /**
     * Will add "Accept-Encoding" into the "Vary" header
     *
     * Note: Ensures caches serve the correct version (compressed or not) based on the
     *       client's "Accept-Encoding" header. Prevents issues with shared caches.
     *
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    private function addAcceptEncodingToVary(ResponseInterface $response): ResponseInterface
    {
        if ($response->hasHeader('Vary')) {
            $existing = $response->getHeaderLine('Vary');
            if (!str_contains($existing, 'Accept-Encoding')) {
                $response = $response->withHeader('Vary', $existing . ', Accept-Encoding');
            }
        } else {
            $response = $response->withHeader('Vary', 'Accept-Encoding');
        }

        return $response;
    }

    /**
     * Check if gzip is applicable
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return bool
     */
    private function canUseGzip(ServerRequestInterface $request, ResponseInterface $response): bool
    {
        $acceptEncoding = $request->getHeaderLine('Accept-Encoding');
        $hasGzip = function_exists('gzencode') &&  str_contains($acceptEncoding, 'gzip');
        return $hasGzip && !$response->hasHeader('Content-Encoding') && $response->getBody()->isSeekable();
    }
}
