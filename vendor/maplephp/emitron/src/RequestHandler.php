<?php

namespace MaplePHP\Emitron;

use MaplePHP\Container\Reflection;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RequestHandler implements RequestHandlerInterface
{
    private int $index = 0;
    private array $middlewareQueue;
    private ResponseFactoryInterface $responseSource;

    public function __construct(array $middlewares, ResponseFactoryInterface $responseFactory)
    {
        $this->middlewareQueue = $middlewares;
        $this->responseSource = $responseFactory;
    }

    /**
     * Process middlewares and middleware that is a string class then it will use the
     * dependency injector in the constructor.
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws \ReflectionException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (!isset($this->middlewareQueue[$this->index])) {
            return $this->responseSource->createResponse(200);
        }

        $middleware = $this->middlewareQueue[$this->index];
        $this->index++;

        if (is_string($middleware)) {
            $reflect = new Reflection($middleware);
            $middleware = $reflect->dependencyInjector();
        }
        return $middleware->process($request, $this);
    }
}