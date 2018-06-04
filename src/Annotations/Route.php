<?php declare(strict_types=1);

namespace ReactiveApps\Command\HttpServer\Annotations;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
final class Route
{
    /**
     * @var string
     */
    private $route;

    /**
     * @param string[] $routes
     */
    public function __construct(string ...$routes)
    {
        $this->route = current($routes);
    }

    /**
     * @return string
     */
    public function getRoute(): string
    {
        return $this->route;
    }
}