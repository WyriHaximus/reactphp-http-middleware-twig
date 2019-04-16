<?php declare(strict_types=1);

namespace ReactiveApps\Command\HttpServer\Middleware;

use Cake\Collection\Collection;
use Doctrine\Common\Annotations\AnnotationReader;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;
use Middlewares\Utils\Factory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReactiveApps\Command\HttpServer\Annotations\Method;
use ReactiveApps\Command\HttpServer\Annotations\Routes;
use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflector\ClassReflector;
use Roave\BetterReflection\SourceLocator\Type\SingleFileSourceLocator;
use function WyriHaximus\from_get_in_packages_composer;
use function WyriHaximus\toChildProcessOrNotToChildProcess;
use function WyriHaximus\toCoroutineOrNotToCoroutine;
use function WyriHaximus\toThreadOrNotToThread;

final class ControllerMiddleware
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var Dispatcher
     */
    private $router;

    /** @var array */
    private $routes = [];

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        $this->router = simpleDispatcher(function (RouteCollector $routeCollector): void {
            foreach ($this->locateRoutes(from_get_in_packages_composer('extra.reactive-apps.http-controller')) as $routes) {
                foreach ($routes['routes'] as $route) {
                    $routeCollector->addRoute($routes['method'], $route, $routes['handler']);
                }
                $this->routes[$routes['handler']] = [
                    'annotations' => $routes['annotations'],
                    'static' => $routes['static'],
                ];
            }
        });
    }

    public function __invoke(ServerRequestInterface $request, callable $next)
    {
        $route = $this->router->dispatch($request->getMethod(), $request->getUri()->getPath());

        if ($route[0] === Dispatcher::NOT_FOUND) {
            return Factory::createResponse(404);
        }

        if ($route[0] === Dispatcher::METHOD_NOT_ALLOWED) {
            return Factory::createResponse(405)->withHeader('Allow', \implode(', ', $route[1]));
        }

        foreach ($route[2] as $name => $value) {
            $request = $request->withAttribute($name, $value);
        }

        $request = $request
            ->withAttribute('request-handler', $route[1])
            ->withAttribute('request-handler-annotations', $this->routes[$route[1]]['annotations'])
            ->withAttribute('request-handler-static', $this->routes[$route[1]]['static'])
        ;

        return $next($request);
    }

    private function locateRoutes(iterable $controllers): iterable
    {
        foreach ($controllers as $controller) {
            yield from self::locateRoute($controller);
        }
    }

    private function locateRoute(string $controller): iterable
    {
        if (\strpos($controller, '*') !== false) {
            yield from self::locateRoutes(\glob($controller));

            return;
        }

        yield from $this->controllerRoutes($controller);
    }

    private function controllerRoutes(string $controller)
    {
        $annotationReader = new AnnotationReader();
        $betterReflection = new BetterReflection();
        $astLocator = $betterReflection->astLocator();
        $reflector = new ClassReflector(new SingleFileSourceLocator($controller, $astLocator));
        foreach ($reflector->getAllClasses() as $class) {
            foreach ($class->getMethods() as $method) {
                $annotations = (new  Collection($annotationReader->getMethodAnnotations((new \ReflectionClass($class->getName()))->getMethod($method->getShortName()))))
                    ->indexBy(function (object $annotation) {
                        return \get_class($annotation);
                    })->toArray();

                if (!isset($annotations[Method::class]) || !isset($annotations[Routes::class])) {
                    continue;
                }

                $requestHandler = $class->getName() . '::' . $method->getName();

                yield [
                    'handler' => $requestHandler,
                    'static' => $method->isStatic(),
                    'routes' => $annotations[Routes::class]->getRoutes(),
                    'method' => $annotations[Method::class]->getMethod(),
                    'annotations' => [
                        'childprocess' => toChildProcessOrNotToChildProcess($requestHandler, $annotationReader),
                        'coroutine' => toCoroutineOrNotToCoroutine($requestHandler, $annotationReader),
                        'thread' => toThreadOrNotToThread($requestHandler, $annotationReader),
                    ],
                ];
            }
        }
    }
}
