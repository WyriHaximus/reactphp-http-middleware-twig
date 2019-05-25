<?php declare(strict_types=1);

namespace ReactiveApps\Command\HttpServer\Routing;

use Cake\Collection\Collection;
use Doctrine\Common\Annotations\AnnotationReader;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;
use Psr\Container\ContainerInterface;
use ReactiveApps\Command\HttpServer\Annotations\Method;
use ReactiveApps\Command\HttpServer\Annotations\Routes;
use ReactiveApps\Command\HttpServer\Annotations\Template;
use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflector\ClassReflector;
use Roave\BetterReflection\SourceLocator\Type\SingleFileSourceLocator;
use function WyriHaximus\from_get_in_packages_composer;
use function WyriHaximus\toChildProcessOrNotToChildProcess;
use function WyriHaximus\toCoroutineOrNotToCoroutine;
use function WyriHaximus\toThreadOrNotToThread;

/**
 * @internal
 */
final class Collector
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
                    'template' => $routes['template'],
                ];
            }
        });
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
            /** @var iterable $files */
            $files = \glob($controller);
            yield from self::locateRoutes($files);

            return;
        }

        yield from $this->controllerRoutes($controller);
    }

    private function controllerRoutes(string $controller): iterable
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
                    'template' => isset($annotations[Template::class]) ? $annotations[Template::class]->getTemplate() : false,
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
