<?php

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use ReactiveApps\Command\HttpServer\ControllerMiddleware;
use ReactiveApps\Command\HttpServer\Command\HttpServer;
use ReactiveApps\Command\HttpServer\RequestHandlerMiddleware;
use ReactiveApps\Rx\Shutdown;
use WyriHaximus\PSR3\ContextLogger\ContextLogger;
use WyriHaximus\React\Http\Middleware\WebrootPreloadMiddleware;
use WyriHaximus\React\Http\PSR15MiddlewareGroup\Factory;

return [
    HttpServer::class => \DI\factory(function (
        LoopInterface $loop,
        LoggerInterface $logger,
        Shutdown $shutdown,
        ContainerInterface $container,
        string $address,
        array $middlwarePrefix = [],
        array $middlwareSuffix = [],
        string $public = null,
        bool $hsts = false
    ) {
        $middleware = [];
        array_push($middleware, ...$middlwarePrefix);
        $middleware[] = Factory::create(
            $loop,
            $logger,
            [
                'hsts' => $hsts,
            ]
        );
        if ($public !== null && file_exists($public) && is_dir($public)) {
            $middleware[] = new WebrootPreloadMiddleware(
                $public,
                new ContextLogger($logger, ['section' => 'webroot'], 'webroot')
            );
        }
        array_push($middleware, ...$middlwareSuffix);
        $middleware[] = new ControllerMiddleware($container);
        $middleware[] = new RequestHandlerMiddleware($loop);

        return new HttpServer($loop, $logger, $shutdown, $address, $middleware);
    })
    ->parameter('address', \DI\get('config.http-server.address'))
    ->parameter('public', \DI\get('config.http-server.public'))
    ->parameter('hsts', \DI\get('config.http-server.hsts'))
    ->parameter('middlwarePrefix', \DI\get('config.http-server.middleware.prefix'))
    ->parameter('middlwareSuffix', \DI\get('config.http-server.middleware.suffix')),
];
