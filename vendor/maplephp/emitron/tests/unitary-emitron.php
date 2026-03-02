<?php

use MaplePHP\Emitron\Emitters\HttpEmitter;
use MaplePHP\Emitron\Middlewares\ContentLengthMiddleware;
use MaplePHP\Emitron\Middlewares\GzipMiddleware;
use MaplePHP\Emitron\Middlewares\HeadRequestMiddleware;
use MaplePHP\Emitron\Middlewares\OutputMiddleware;
use MaplePHP\Emitron\RequestHandler;
use MaplePHP\Http\Environment;
use MaplePHP\Http\ResponseFactory;
use MaplePHP\Http\ServerRequest;
use MaplePHP\Http\Stream;
use MaplePHP\Http\Uri;
use MaplePHP\Unitary\{Config\TestConfig, Expect, TestCase};

$config = TestConfig::make()->withName("emitron");
group($config->withSubject("Testing middleware and emitter"), function (TestCase $case) {

    $stream = new Stream(Stream::TEMP);
    $stream->write("Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras eleifend ligula vel diam tincidunt finibus. In dapibus dictum lectus a malesuada.");

    // It will reverse the order
    $middlewares = [
        new ContentLengthMiddleware(),
        new GzipMiddleware(),
        new OutputMiddleware($stream),
        new HeadRequestMiddleware(),
    ];

    $env = new Environment();
    $uri = new Uri($env->getUriParts());
    $request = new ServerRequest($uri, $env);

    // Do not exist in CLI so need to mock it
    $request = $request->withHeader("Accept-Encoding", "gzip");

    $factory = new ResponseFactory();
    $factory->createResponse();
    $handler = new RequestHandler($middlewares, $factory);
    $response = $handler->handle($request);


    $emit = new HttpEmitter();

    ob_start();
    $emit->emit($response->withBody($stream), $request);
    $out = ob_get_clean();


    $case->validate($out, function (Expect $expect) {
        $expect->isLength(143);
    });

    $headers = $response->getHeaders();
    $case->validate($headers['vary'][0] ?? null, function (Expect $expect) {
        $expect->isEqualTo('Accept-Encoding');
    });

    $case->validate($headers['content-encoding'][0] ?? null, function (Expect $expect) {
        $expect->isEqualTo('gzip');
    });

    $case->validate($headers['content-length'][0] ?? 0, function (Expect $expect) {
        $expect->isLooselyEqualTo(126);
    });
});

