<?php

use Psr\Container\ContainerInterface;
use React\Promise\PromiseInterface;
use ReactiveApps\Command\HttpServer\ControllerMiddleware;
use ReactiveApps\Command\HttpServer\RequestHandlerMiddleware;
use WyriHaximus\Recoil\QueueCallerInterface;

return [
    RequestHandlerMiddleware::class => \DI\factory(function (
        QueueCallerInterface $queueCaller,
        PromiseInterface $childProcessPool,
        ContainerInterface $container
    ) {
        return new RequestHandlerMiddleware($queueCaller, $childProcessPool, $container);
    })
        ->parameter('childProcessPool', \DI\get('internal.http-server.child-process.pool')),
    ControllerMiddleware::class => \DI\factory(function (ContainerInterface $container) {
        return new ControllerMiddleware($container);
    }),
];
