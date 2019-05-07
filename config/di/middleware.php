<?php declare(strict_types=1);

use Psr\Container\ContainerInterface;
use React\Promise\PromiseInterface;
use ReactiveApps\Command\HttpServer\Middleware\ChildProcessMiddleware;
use ReactiveApps\Command\HttpServer\Middleware\ControllerMiddleware;
use ReactiveApps\Command\HttpServer\Middleware\CoroutineMiddleware;
use ReactiveApps\Command\HttpServer\Middleware\RequestHandlerMiddleware;
use ReactiveApps\Command\HttpServer\Middleware\TemplateRenderMiddleware;
use ReactiveApps\Command\HttpServer\Middleware\ThreadMiddleware;
use Twig\Environment;
use WyriHaximus\React\Http\Middleware\MiddlewareRunner;
use WyriHaximus\React\Parallel\PoolInterface;

return [
    ChildProcessMiddleware::class => \DI\factory(function (
        PromiseInterface $childProcessPool
    ) {
        return new ChildProcessMiddleware($childProcessPool);
    })
        ->parameter('childProcessPool', \DI\get('internal.http-server.child-process.pool')),
    ControllerMiddleware::class => \DI\factory(function (ContainerInterface $container) {
        return new ControllerMiddleware($container);
    }),
    TemplateRenderMiddleware::class => \DI\factory(function (
        Environment $twig,
        string $version
    ) {
        $twig->addGlobal('version', $version);

        return new TemplateRenderMiddleware($twig);
    })
        ->parameter('version', \DI\get('config.app.version')),
    ThreadMiddleware::class => \DI\factory(function (
        PoolInterface $pool
    ) {
        return new ThreadMiddleware($pool);
    })
        ->parameter('pool', \DI\get('internal.http-server.thread.pool')),
    'internal.http-server.request-handling-middleware-runner' => \DI\factory(function (ContainerInterface $container) {
        $middleware = [];

        $middleware[] = $container->get(ControllerMiddleware::class);
        $middleware[] = $container->get(TemplateRenderMiddleware::class);
        $middleware[] = $container->get(CoroutineMiddleware::class);
        if (\extension_loaded('parallel') && \interface_exists(PoolInterface::class)) {
            $middleware[] = $container->get(ThreadMiddleware::class);
        }
        $middleware[] = $container->get(ChildProcessMiddleware::class);
        $middleware[] = $container->get(RequestHandlerMiddleware::class);

        return new MiddlewareRunner(...$middleware);
    }),
];
