<?php

declare(strict_types=1);

namespace MaplePHP\Core\Router;

use MaplePHP\Emitron\Contracts\RouterInterface;
use MaplePHP\Http\Interfaces\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;

use function FastRoute\cachedDispatcher;
use function FastRoute\simpleDispatcher;

class RouterDispatcher implements RouterInterface
{
    public const FOUND = Dispatcher::FOUND;
    public const NOT_FOUND = Dispatcher::NOT_FOUND;
    public const METHOD_NOT_ALLOWED = Dispatcher::METHOD_NOT_ALLOWED;

    private ?ResponseInterface $response;
    private ServerRequestInterface $request;
    private array $router;
    private ?string $routerCacheFile = null;
    private bool $enableCache = false;
    private ?Dispatcher $dispatcher = null;
    private string $dispatchPath = "";
    private string $method;
    //private array $middlewares = [];

    /**
     * Router Dispatcher, Used to make it easier to change out router library
     */
    public function __construct(ServerRequestInterface $request, ?ResponseInterface $response = null)
    {
        $this->response = $response;
        $this->request = $request;
        $this->method = $this->request->getMethod();
    }

    /**
     * Change request method to a static method
     * @param string $method
     * @return void
     */
    public function setRequestMethod(string $method): void
    {
        $this->method = $method;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Set URL router dispatch path
     * @param string $path
     * @return void
     */
    public function setDispatchPath(string $path): void
    {
        $this->dispatchPath = "/" . ltrim($path, "/");
    }

    /**
     * Cache the router data to a cache file for increased performance.
     * But remember you need to clear the file if you make router changes!
     * @param string $cacheFile
     * @param bool $enableCache (Default true)
     * @return void
     * @throws \Exception
     */
    public function setRouterCacheFile(string $cacheFile, bool $enableCache = true): void
    {
        $this->routerCacheFile = $cacheFile;
        $this->enableCache = $enableCache;
        $dir = dirname($this->routerCacheFile);

        if ($this->enableCache && !is_writable($dir)) {
            throw new \Exception("Directory (\"$dir/\") is not writable. " .
                "Could not save \"$this->routerCacheFile\" file.", 1);
        }
    }

    /**
     * The is used to nest group routes
     * The param "Order" here is important.
     * Callable: Routes to be bound to pattern or middlewares
     * Pattern: Routes bound to pattern
     * Array: Middlewares
     * @param  mixed  $arg1 Callable/Pattern
     * @param  mixed  $arg2 Callable/array
     * @param  array  $arg3 array
     * @return void
     */
    public function group($arg1, $arg2, $arg3 = []): void
    {
        $inst = clone $this;
        $inst->router = [];
        $pattern = (is_string($arg1)) ? $arg1 : null;
        $call = ($pattern) ? $arg2 : $arg1;
        $data = ($pattern) ? $arg3 : $arg2;

        if (!is_array($data)) {
            $data = [];
        }
        if (!is_callable($call)) {
            throw new \InvalidArgumentException("Either the argument 1 or 2 need to be callable.", 1);
        }

        $this->router[] = function () use (&$inst, $pattern, $call, $data) {
            $call($inst, $data);
            return [
                "router" => $inst,
                "data" => $data,
                "pattern" => $pattern
            ];
        };
    }

    /**
     * @param string|array $methods
     * @param string $pattern
     * @param string|array|callable $controller
     * @return void
     */
    public function map(string|array $methods, string $pattern, string|array|callable $controller): void
    {
        $this->router[] = new RoutingManager($methods, $pattern, $controller);
    }

    /**
     * Map GET method router and attach controller to it's pattern
     * @param  string $pattern          Example: /about, /{page:about}, /{page:.+}, /{category:[^/]+}, /{id:\d+}
     * @param  string|array|callable $controller Attach a controller (['Name\Space\ClassName', 'methodName'])
     * @return void
     */
    public function get(string $pattern, string|array|callable $controller): void
    {
        $this->map("GET", $pattern, $controller);
    }

    /**
     * Map POST method router and attach controller to it's pattern
     * @param  string $pattern          Example: /about, /{page:about}, /{page:.+}, /{category:[^/]+}, /{id:\d+}
     * @param  string|array|callable $controller Attach a controller (['Name\Space\ClassName', 'methodName'])
     * @return void
     */
    public function post(string $pattern, string|array|callable $controller): void
    {
        $this->map("POST", $pattern, $controller);
    }

    /**
     * Map PUT method router and attach controller to it's pattern (Se GET/POST for example)
     * @param  string $pattern
     * @param  string|array|callable $controller
     * @return void
     */
    public function put(string $pattern, string|array|callable $controller): void
    {
        $this->map("PUT", $pattern, $controller);
    }

    /**
     * Map DELETE method router and attach controller to it's pattern (Se GET/POST for example)
     * @param  string $pattern
     * @param  string|array|callable $controller
     * @return void
     */
    public function delete(string $pattern, string|array|callable $controller): void
    {
        $this->map("DELETE", $pattern, $controller);
    }

    /**
     * Create a shell/cli route
     * @param  string $pattern
     * @param  string|array|callable $controller
     * @return void
     */
    public function shell(string $pattern, string|array|callable $controller): void
    {
        $this->map("SHELL", $pattern, $controller);
    }

    /**
     * Create a shell/cli route
     * @param  string $pattern
     * @param  string|array|callable $controller
     * @return void
     */
    public function cli(string $pattern, string|array|callable $controller): void
    {
        $this->shell($pattern, $controller);
    }

    /**
     * Will feed the Dispatcher with routes
     * @return callable
     */
    public function dispatcherCallback(): callable
    {
        return function (RouteCollector $route) {
            foreach ($this->router as $r) {
                if (is_callable($r)) {
                    $inst = $r();
                    $this->dispatcherNest($route, $inst);
                } else {
                    $this->addRoute($route, $r);
                }
            }
        };
    }

    protected function addRoute(RouteCollector $route, RoutingManager $routeItem, ?array $controller = null): void
    {
        $method = ($routeItem->getMethod() === "SHELL") ? "GET" : $routeItem->getMethod();
        $controller = ($controller === null) ? $routeItem->getController() : $controller;
        $route->addRoute($method, $routeItem->getPattern(), $controller);
    }

    /**
     * Register the dispatcher
     * @return Dispatcher
     */
    protected function registerDispatcher(): Dispatcher
    {
        if ($this->dispatcher === null) {
            if ($this->routerCacheFile === null) {
                $this->dispatcher = simpleDispatcher($this->dispatcherCallback());
            } else {
                $this->dispatcher = cachedDispatcher($this->dispatcherCallback(), [
                    'cacheFile' => $this->routerCacheFile,
                    'cacheDisabled' => !$this->enableCache
                ]);
            }
        }
        return $this->dispatcher;
    }

    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    public function loadMid()
    {
    }

    /**
     * Dispatch results
     * @param callable $call
     * @return ResponseInterface
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function dispatch(callable $call): bool
    {
        $dispatcher = $this->registerDispatcher();
        $routeInfo = $dispatcher->dispatch($this->method, $this->dispatchPath);

        //print_r($routeInfo);
        if ($routeInfo[0] === Dispatcher::FOUND) {
            //die("OK");
            $call($routeInfo, $this->response, $this->request);
        }


        //die("BOO");



        return true;

        /*
         if ($routeInfo[0] === Dispatcher::FOUND) {


            if (is_array($routeInfo[1]['controller'])) {

                $select = (isset($routeInfo[1]['controller'])) ? $routeInfo[1]['controller'] : $routeInfo[1];
                if (!class_exists($select[0])) {
                    throw new \Exception("You have specified a controller ($select[0]) that do not exists in you router file!", 1);
                }
                $reflect = new Reflection($select[0]);
                $controller = $reflect->dependencyInjector();
                if (isset($select[1])) {
                    // Add Dependency Injector on the class method.
                    $response = $reflect->dependencyInjector($controller, $select[1]);
                } else {
                    if (is_callable($controller)) {
                        $response = $controller($this->response, $this->request);
                    }
                }

                if ($response instanceof ResponseInterface) {
                    $this->response = $response;
                }

            } else {
                $response = $routeInfo[1]['controller']($this->response, $this->request);
            }

        } else {
            $response = $call($routeInfo[0], $this->response, $this->request, null);
        }

        if ($response instanceof ResponseInterface) {
            $this->response = $response;
        }

        if (is_array($response)) {
            $this->response = $this->jsonResponse($response);
        }
        return $this->response;
         */
    }

    /**
     * Set response as json response
     * @param array $data
     * @return ResponseInterface
     */
    protected function jsonResponse(array $data): ResponseInterface
    {
        $response = $this->response->withHeader("Content-type", "application/json; charset=UTF-8");
        $response->getBody()->write(json_encode($data));
        return $response;
    }

    /**
     * @dispatcherNest will handle all nested grouped routes
     * @param  RouteCollector   $route
     * @param  array            $inst
     * @return void
     */
    protected function dispatcherNest(RouteCollector $route, array $inst): void
    {
        if ($inst['pattern'] !== null) {
            $route->addGroup($inst['pattern'], function (RouteCollector $route) use ($inst) {
                foreach ($inst['router']->router as $g) {
                    if (($g instanceof RoutingManager)) {
                        $this->addRoute($route, $g, $g->getMiddleware($inst['data']));
                        //$route->addRoute($g->getMethod(), $g->getPattern(), $g->getMiddleware($inst['data']));
                    } else {
                        if (is_callable($g)) {
                            $newInst = $g();
                            $newInst['data'] = array_merge($inst['data'], $newInst['data']);
                            $this->dispatcherNest($route, $newInst);
                        }
                    }
                }
            });
        } else {
            foreach ($inst['router']->router as $g) {
                if (($g instanceof RoutingManager)) {
                    $this->addRoute($route, $g, $g->getMiddleware($inst['data']));
                    //$route->addRoute($g->getMethod(), $g->getPattern(), $g->getMiddleware($inst['data']));
                } else {
                    if (is_callable($g)) {
                        $newInst = $g();
                        $newInst['data'] = array_merge($inst['data'], $newInst['data']);
                        $this->dispatcherNest($route, $newInst);
                    }
                }
            }
        }
    }

    /**
     * Will dispatch the middleware at the right position
     * @param  ?array $data List of middlewares
     * @param callable $call inject routers
     * @return void
     * @throws /Exception
     */
    protected function dispatchMiddleware(?array $data, $call): void
    {

    }
}
