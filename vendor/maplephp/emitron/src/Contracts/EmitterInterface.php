<?php

declare(strict_types=1);

namespace MaplePHP\Emitron\Contracts;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface EmitterInterface
{
    /**
     * Emits the final response to the client.
     *
     * Note: This method is expected to be the final step in the response lifecycle.
     * Once called, headers and body are sent and can no longer be modified.
     *
     * @param ResponseInterface $response
     * @param ServerRequestInterface $request
     * @return void
     */
    public function emit(ResponseInterface $response, ServerRequestInterface $request): void;
}
