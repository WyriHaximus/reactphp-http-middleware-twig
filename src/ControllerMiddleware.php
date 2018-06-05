<?php declare(strict_types=1);

namespace ReactiveApps\Command\HttpServer;

use Composed\Package;
use function Composed\packages;
use Doctrine\Common\Annotations\AnnotationReader;
use function igorw\get_in;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflector\ClassReflector;
use Roave\BetterReflection\SourceLocator\Type\SingleFileSourceLocator;

final class ControllerMiddleware
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        $routes = $this->routes();
    }

    private function routes(): iterable
    {
        foreach ($this->locations() as $controller) {
            yield from $this->controllerRoutes($controller);
        }
    }

    private function locations(): iterable
    {
        /** @var Package $package */
        foreach (packages(true) as $package) {
            $config = $package->getConfig('extra');

            if ($config === null) {
                continue;
            }

            $commands = get_in(
                $config,
                [
                    'reactive-apps',
                    'http-controller',
                ]
            );

            if ($commands === null) {
                continue;
            }

            foreach ($commands as $namespace => $path) {
                yield $package->getPath($path) => $namespace;
            }
        }
    }

    private function controllerRoutes(string $controller)
    {
        $annotationReader = new AnnotationReader();
        $betterReflection = new BetterReflection();
        $astLocator = $betterReflection->astLocator();
        $reflector = new ClassReflector(new SingleFileSourceLocator($controller, $astLocator));
        foreach ($reflector->getAllClasses() as $class) {
            foreach ($class->getMethods() as $method) {
                $annotations = $annotationReader->getMethodAnnotations($method);
            }
        }

    }

    public function __invoke(ServerRequestInterface $request, callable $next)
    {

    }
}
