<?php

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use ReactiveApps\Command\HttpServer\Command\HttpServer;
use ReactiveApps\Command\HttpServer\ControllerMiddleware;
use ReactiveApps\Command\HttpServer\RequestHandlerMiddleware;
use ReactiveApps\Rx\Shutdown;
use WyriHaximus\PSR3\ContextLogger\ContextLogger;
use WyriHaximus\React\Http\Middleware\RewriteMiddleware;
use WyriHaximus\React\Http\Middleware\WebrootPreloadMiddleware;
use WyriHaximus\React\Http\PSR15MiddlewareGroup\Factory;

return [
    RequestHandlerMiddleware::class => \DI\factory(function (
        LoopInterface $loop,
        PromiseInterface $childProcessPool
    ) {
        return new RequestHandlerMiddleware($loop, $childProcessPool);
    })
    ->parameter('childProcessPool', \DI\get('internal.http-server.child-process.pool')),
    ControllerMiddleware::class => \DI\factory(function (ContainerInterface $container) {
        return new ControllerMiddleware($container);
    }),
];
