# MaplePHP Emitron

Emitron is a modern PSR-based middleware and kernel library designed to handle every step of the HTTP lifecycle, from incoming request to emitted response. It’s built for developers who want clean architecture, predictable behavior, and full control without having to reinvent the wheel.

Out of the box, Emitron provides a complete runtime foundation that follows best practices across PSR-7, PSR-11, and PSR-15. It includes a configurable middleware pipeline, an emitter that handles headers and body output, and a kernel that automatically boots your container, initializes requests, responses, streams, and emits the final output.

Whether you’re building your own framework, an HTTP microservice, or a CLI-driven application, Emitron gives you a consistent, extensible core that plays well with any PSR-compliant ecosystem — simple when you need it, powerful when you don’t.

---

## Features

* Fully **PSR-15 compliant** middleware dispatcher
* Works with any **PSR-7** request and response library
* Lightweight and **framework-agnostic**
* Supports **automatic bootstrapping** via the `Kernel`
* Built-in middlewares for common HTTP tasks (output buffering, compression, content length, HEAD requests, etc.)
* Compatible with **any PSR-11 container**

---

## Installation

```bash
composer require maplephp/emitron
```

---

## Middleware Example

Emitron includes a robust request handler that executes PSR-15 middlewares in sequence, returning a fully PSR-7 compliant response.

```php
use MaplePHP\Emitron\RequestHandler;use MaplePHP\Http\Environment;use MaplePHP\Http\ServerRequest;use MaplePHP\Http\Uri;

// Use MaplePHP HTTP library or any other PSR-7 implementation
$env = new Environment();
$request = new ServerRequest(new Uri($env->getUriParts($parts)), $env);

// Add your middlewares — Emitron ships with several ready-to-use
$middlewares = [
    new OutputBufferMiddleware(),   // Inject ob_get_clean() into stream
    new GzipMiddleware(),           // Compress body, update Content-Encoding
    new ContentLengthMiddleware(),  // Recalculate body size
    new HeadRequestMiddleware(),    // Optionally blank body
    new EmitterMiddleware(),        // Emit headers + body
];

// Run the middleware stack
$handler = new RequestHandler($middlewares, $factory);
$response = $handler->handle($request);
```

Each middleware conforms to `Psr\Http\Server\MiddlewareInterface`, allowing you to plug in your own or third-party middlewares with no additional setup.

---

## Kernel and Emitter

Emitron’s `Kernel` provides an all-in-one entry point for bootstrapping and executing your application.
It automatically initializes the **request**, **response**, **stream**, **container**, and **middlewares**, following PSR conventions.

```php
use MaplePHP\Emitron\Kernel;use MaplePHP\Http\Environment;use MaplePHP\Http\ServerRequest;use MaplePHP\Http\Uri;

$env = new Environment();
$request = new ServerRequest(new Uri($env->getUriParts($parts)), $env);

$kernel = new Kernel(new Container(), [
    new OutputBufferMiddleware(),
    new GzipMiddleware(),
    new ContentLengthMiddleware(),
    new HeadRequestMiddleware(),
    new EmitterMiddleware()
]);

$kernel->run($request);
```

The `Kernel` takes three arguments:

```php
$kernel = new Kernel(
    Psr\Container\ContainerInterface, 
    [Psr\Http\Server\MiddlewareInterface::class], 
    ?MaplePHP\Emitron\Contracts\DispatchConfigInterface
);
```

---

## ⚙️ Custom Configuration

Emitron supports custom configuration files.
Provide a PHP file that returns an array, and Emitron will pass it to the PSR container under the key `'configuration'`.

```php
use MaplePHP\Emitron\DispatchConfig;use MaplePHP\Emitron\Kernel;

$config = new DispatchConfig(__DIR__ . '/config/app.php');

$kernel = new Kernel(new Container(), [
    new ContentLengthMiddleware(),
    new HeadRequestMiddleware(),
], $config);
// You can also return the path in you app if you want with:
// $configPath = Kernel::getConfigFilePath();
```

Example `config/app.php`:

```php
<?php

return [
    'app_name' => 'MyApp',
    'debug' => true,
    'timezone' => 'Europe/Stockholm',
];
```

---

