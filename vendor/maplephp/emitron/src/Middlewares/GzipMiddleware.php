<?php

declare(strict_types=1);

namespace MaplePHP\Emitron\Middlewares;

use MaplePHP\Http\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class GzipMiddleware implements MiddlewareInterface
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

        if ($this->canUseGzip($request, $response)) {

            $body = $response->getBody();
            $body->rewind();
            $raw = $body->getContents();
            $gzipped = gzencode($raw, 9, defined("FORCE_GZIP") ? FORCE_GZIP : 31);

            // Most PSR Libraries use this initialization
            // If not then use MaplePHP Http or create you own Gzip middleware
            $gzStream = new Stream(fopen('php://temp', 'r+'));
            $gzStream->write($gzipped);

            $response = $this
                ->addAcceptEncodingToVary($response)
                ->withBody($gzStream)
                ->withHeader('Content-Encoding', 'gzip');
        }

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
