<?php

namespace MaplePHP\Emitron\Contracts;

use Psr\Http\Message\ServerRequestInterface;

interface KernelInterface
{
    /**
     * Run the emitter and init all routes, middlewares and configs
     *
     * @param ServerRequestInterface $request
     * @return void
     */
    public function run(ServerRequestInterface $request): void;

}
