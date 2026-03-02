<?php

/**
 * Unit — Part of the MaplePHP Unitary Kernel/ Dispatcher,
 * A simple and fast dispatcher, will work great for this solution
 *
 * @package:    MaplePHP\Unitary
 * @author:     Daniel Ronkainen
 * @licence:    Apache-2.0 license, Copyright © Daniel Ronkainen
 *              Don't delete this comment, it's part of the license.
 */

declare(strict_types=1);

namespace MaplePHP\Emitron;

use MaplePHP\Container\Reflection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use MaplePHP\Log\InvalidArgumentException;

class Kernel extends AbstractKernel
{
    /**
     * Run the emitter and init all routes, middlewares and configs
     *
     * @param ServerRequestInterface $request
     * @param StreamInterface|null $stream
     * @return void
     */
    public function run(ServerRequestInterface $request, ?StreamInterface $stream = null): void
    {
        $this->dispatchConfig->getRouter()->dispatch(function ($data, $args, $middlewares) use ($request, $stream) {

            if (!isset($data['handler'])) {
                throw new InvalidArgumentException("The router dispatch method arg 1 is missing the 'handler' key.");
            }

            $this->container->set("request", $request);
            $this->container->set("args", $args);
            $this->container->set("configuration", $this->getDispatchConfig());

            $response = $this->initRequestHandler($request, $this->getBody($stream), $middlewares);

            $controller = $data['handler'];
            if (!isset($controller[1])) {
                $controller[1] = '__invoke';
            }
            if (count($controller) === 2) {
                [$class, $method] = $controller;
                if (method_exists($class, $method)) {
                    $reflect = new Reflection($class);
                    $classInst = $reflect->dependencyInjector();
                    // Can replace the active Response instance through Command instance
                    $hasNewResponse = $reflect->dependencyInjector($classInst, $method);
                    $response = ($hasNewResponse instanceof ResponseInterface) ? $hasNewResponse : $response;

                } else {
                    $response->getBody()->write("\nERROR: Could not load Controller class {$class} and method {$method}()\n");
                }
            }
            $this->createEmitter()->emit($response, $request);
        });
    }
}