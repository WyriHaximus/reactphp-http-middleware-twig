<?php

use Psr\Container\ContainerInterface;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use ReactiveApps\Command\HttpServer\Middleware\ChildProcessMiddleware;
use ReactiveApps\Command\HttpServer\Middleware\ControllerMiddleware;
use ReactiveApps\Command\HttpServer\Middleware\CoroutineMiddleware;
use ReactiveApps\Command\HttpServer\Middleware\RequestHandlerMiddleware;
use ReactiveApps\Command\HttpServer\Middleware\ThreadMiddleware;
use WyriHaximus\React\Http\Middleware\MiddlewareRunner;
use WyriHaximus\React\Parallel\Finite;
use WyriHaximus\React\Parallel\PoolInterface;
use WyriHaximus\Recoil\QueueCallerInterface;

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
    CoroutineMiddleware::class => \DI\factory(function (
        QueueCallerInterface $queueCaller,
        ContainerInterface $container
    ) {
        return new CoroutineMiddleware($queueCaller, $container);
    })
        ->parameter('childProcessPool', \DI\get('internal.http-server.child-process.pool')),
    ThreadMiddleware::class => \DI\factory(function (
        LoopInterface $loop
    ) {
        return new ThreadMiddleware(new Finite($loop, 32));
    }),
    'internal.http-server.request-handling-middleware-runner' => \DI\factory(function (ContainerInterface $container) {
        $middleware = [];

        $middleware[] = $container->get(ControllerMiddleware::class);
        $middleware[] = $container->get(CoroutineMiddleware::class);
        if (extension_loaded('parallel') && interface_exists(PoolInterface::class)) {
            $middleware[] = $container->get(ThreadMiddleware::class);
        }
        $middleware[] = $container->get(ChildProcessMiddleware::class);
        $middleware[] = $container->get(RequestHandlerMiddleware::class);

        return new MiddlewareRunner(...$middleware);
    }),
];
