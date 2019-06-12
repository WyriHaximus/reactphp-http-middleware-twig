<?php declare(strict_types=1);

namespace ReactiveApps\Command\HttpServer\Middleware;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;
use Middlewares\Utils\Factory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\Promise\PromiseInterface;
use function React\Promise\resolve;
use ReactiveApps\Command\HttpServer\Routing\Collector;
use function RingCentral\Psr7\stream_for;

/**
 * @internal
 */
final class ControllerMiddleware
{
    /** @var ContainerInterface */
    private $container;

    /** @var Dispatcher */
    private $router;

    /** @var array */
    private $routes = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        $this->router = simpleDispatcher(function (RouteCollector $routeCollector): void {
            foreach (Collector::collect() as $routes) {
                foreach ($routes['routes'] as $route) {
                    $routeCollector->addRoute($routes['method'], $route, $routes['handler']);
                }
                $this->routes[$routes['handler']] = [
                    'annotations' => $routes['annotations'],
                    'static' => $routes['static'],
                    'template' => $routes['template'],
                ];
            }
        });
    }

    public function __invoke(ServerRequestInterface $request, callable $next): PromiseInterface
    {
        $route = $this->router->dispatch($request->getMethod(), $request->getUri()->getPath());

        if ($route[0] === Dispatcher::NOT_FOUND) {
            return resolve(
                Factory::createResponse(404)->
                    withHeader('Content-Type', 'text/plain')->
                    withBody(stream_for('Couldn\'t find what you\'re looking for'))
            );
        }

        if ($route[0] === Dispatcher::METHOD_NOT_ALLOWED) {
            return resolve(
                Factory::createResponse(405)->withHeader('Allow', \implode(', ', $route[1]))->
                    withHeader('Content-Type', 'text/plain')->
                    withBody(stream_for('Method not allowed'))
            );
        }

        foreach ($route[2] as $name => $value) {
            $request = $request->withAttribute($name, $value);
        }

        $request = $request
            ->withAttribute('request-handler', $route[1])
            ->withAttribute('request-handler-annotations', $this->routes[$route[1]]['annotations'])
            ->withAttribute('request-handler-static', $this->routes[$route[1]]['static'])
        ;

        if (\is_string($this->routes[$route[1]]['template'])) {
            $request = $request->withAttribute('request-handler-template', $this->routes[$route[1]]['template']);
        }

        return resolve($next($request));
    }
}
