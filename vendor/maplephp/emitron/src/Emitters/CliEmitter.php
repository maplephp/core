<?php

declare(strict_types=1);

namespace MaplePHP\Emitron\Emitters;

use MaplePHP\Emitron\Contracts\EmitterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CliEmitter implements EmitterInterface
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
    public function emit(ResponseInterface $response, ServerRequestInterface $request): void
    {
        $body = $response->getBody();
        if ($body->isSeekable()) {
            if (!$body->getSize() && $this->isSuccessfulResponse($response)) {
                $body->write("No Content\n");
            }
            $body->rewind();
            fwrite(STDOUT, $body->getContents());
        }
        
        exit($this->getCliStatusCode($response));
    }

    /**
     * Get right status code for exit in CLI
     * @param ResponseInterface $response
     * @return int
     */
    private function getCliStatusCode(ResponseInterface $response): int
    {
        if($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            return 0;
        }
        return $response->getStatusCode();
    }

    /**
     * Check if a successful response
     *
     * @param ResponseInterface $response
     * @return bool
     */
    private function isSuccessfulResponse(ResponseInterface $response): bool
    {
        return in_array($response->getStatusCode(), [0, 200, 204], true);
    }
}