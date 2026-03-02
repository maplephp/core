<?php

declare(strict_types=1);

namespace MaplePHP\Emitron\Middlewares;

use MaplePHP\Emitron\Contracts\EmitterInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class EmitterMiddleware implements MiddlewareInterface
{
    private EmitterInterface $emitter;

    public function __construct(EmitterInterface $emitter)
    {
        $this->emitter = $emitter;
    }

    /**
     * Emits the final response to the client.
     *
     * Note: This method is expected to be the final step in the response lifecycle.
     * Once called, headers and body are sent and can no longer be modified.
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        $this->emitter->emit($response, $request);
        return $response;
    }
}
