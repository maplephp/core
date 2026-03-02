<?php

declare(strict_types=1);

namespace MaplePHP\Emitron\Emitters;

use MaplePHP\Emitron\Contracts\EmitterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HttpEmitter implements EmitterInterface
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
        $status = $response->getStatusCode();
        $method = strtoupper($request->getMethod());
        $skipBody = in_array($status, [204, 304]) || ($status >= 100 && $status < 200) || $method === 'HEAD';

        // Default to 204 No Content if nobody was written
        if (!$response->getBody()->getSize() && $this->isSuccessfulResponse()) {
            $response = $response->withStatus(204);
            $response->getBody()->write("No Content\n");
        }

        $this->createHeaders($response);
        if (!$skipBody && $body->isSeekable()) {
            $body->rewind();
        }
        echo $skipBody ? '' : $body->getContents();
    }

    /**
     * Check if a successful response
     *
     * @param ResponseInterface $response
     * @return bool
     */
    private function isSuccessfulResponse(ResponseInterface $response): bool
    {
        return $response->getStatusCode() === 200 || $response->getStatusCode() === 204;
    }
    /**
     * Creates all prepared headers
     *
     * @param ResponseInterface $response
     * @return void
     */
    private function createHeaders(ResponseInterface $response): void
    {
        http_response_code($response->getStatusCode());
        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header("$name: $value", false);
            }
        }
    }
}
