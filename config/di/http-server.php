<?php

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use ReactiveApps\Command\HttpServer\ControllerMiddleware;
use ReactiveApps\Command\HttpServer\HttpServer;
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
        callable $handler,
        array $middlwarePrefix = [],
        array $middlwareSuffix = [],
        string $public = null
    ) {
        $middleware = [];
        array_push($middleware, ...$middlwarePrefix);
        $middleware[] = Factory::create($loop, $logger);
        if ($public !== null && file_exists($public) && is_dir($public)) {
            $middleware[] = new WebrootPreloadMiddleware(
                $public,
                new ContextLogger($logger, ['section' => 'webroot'], 'webroot')
            );
        }
        array_push($middleware, ...$middlwareSuffix);
        $middleware[] = new ControllerMiddleware($container);
        $middleware[] = $handler;

        return new HttpServer($loop, $logger, $shutdown, $address, $middleware);
    })
    ->parameter('address', \DI\get('config.http-server.address'))
    ->parameter('handler', \DI\get('config.http-server.handler'))
    ->parameter('public', \DI\get('config.http-server.public'))
    ->parameter('middlwarePrefix', \DI\get('config.http-server.middleware.prefix'))
    ->parameter('middlwareSuffix', \DI\get('config.http-server.middleware.suffix')),
];
