<?php declare(strict_types=1);

namespace ReactiveApps\Command\HttpServer;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

final class RequestHandlerFactory
{
    /** @var ContainerInterface */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function create(ServerRequestInterface $request): callable
    {
        $requestHandler = $request->getAttribute('request-handler');
        if ($request->getAttribute('request-handler-static') === true) {
            return $requestHandler;
        }

        return (function (string $requestHandler) {
            [$controller, $method] = \explode('::', $requestHandler);

            return [
                $this->container->get($controller),
                $method,
            ];
        })($requestHandler);
    }
}
