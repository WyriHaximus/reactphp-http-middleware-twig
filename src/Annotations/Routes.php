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
     * @param string[]|string[][] $routes
     */
    public function __construct(array $routes)
    {
        if (\is_string($routes['value'])) {
            $this->routes = [$routes['value']];

            return;
        }

        $this->routes = $routes['value'];
    }

    /**
     * @return string[]
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
}
