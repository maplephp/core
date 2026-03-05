<?php

namespace MaplePHP\Core\Interfaces;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ErrorPageInterface
{
    /**
     * You can also use the constructor to pass in props or whatever
     *
     * @param ResponseInterface $response
     * @param ServerRequestInterface $request
     * @param array $context
     * @return string
     */
    public function render(
        ResponseInterface $response,
        ServerRequestInterface $request,
        array $context = []
    ): string;
}