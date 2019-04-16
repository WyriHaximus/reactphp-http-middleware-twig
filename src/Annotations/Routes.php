<?php declare(strict_types=1);

namespace ReactiveApps\Command\HttpServer\Annotations;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
final class Routes
{
    /** @var string[]  */
    private $routes;

    /**
     * @param string[] $routes
     */
    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    /**
     * @return iterable
     */
    public function getRoutes(): iterable
    {
        return $this->routes;
    }
}
