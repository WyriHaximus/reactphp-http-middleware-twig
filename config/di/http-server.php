<?php

use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use React\Socket\Server as SocketServer;
use ReactiveApps\Command\HttpServer\Command\HttpServer;
use ReactiveApps\Command\HttpServer\ControllerMiddleware;
use ReactiveApps\Command\HttpServer\Listener\Shutdown;
use ReactiveApps\Command\HttpServer\RequestHandlerMiddleware;
use WyriHaximus\PSR3\ContextLogger\ContextLogger;
use WyriHaximus\React\Http\Middleware\RewriteMiddleware;
use WyriHaximus\React\Http\Middleware\WebrootPreloadMiddleware;
use WyriHaximus\React\Http\PSR15MiddlewareGroup\Factory;

return [
    'internal.http-server.socket' => \DI\factory(function (
        LoopInterface $loop,
        string $address
    ) {
        return new SocketServer($address, $loop);
    })
        ->parameter('address', \DI\get('config.http-server.address')),
    Shutdown::class => \DI\factory(function (
        SocketServer $socket,
        LoggerInterface $logger
    ) {
        return new Shutdown($socket, $logger);
    })
        ->parameter('socket', \DI\get('internal.http-server.socket')),
    HttpServer::class => \DI\factory(function (
        LoopInterface $loop,
        LoggerInterface $logger,
        ControllerMiddleware $controllerMiddleware,
        RequestHandlerMiddleware $requestHandlerMiddleware,
        SocketServer $socket,
        array $middlwarePrefix = [],
        array $middlwareSuffix = [],
        array $rewrites = [],
        string $public = null,
        bool $hsts = false
    ) {
        $logger = new ContextLogger($logger, ['section' => 'http-server'], 'http-server');
        $middleware = [];
        array_push($middleware, ...$middlwarePrefix);
        $middleware[] = Factory::create(
            $loop,
            $logger,
            [
                'hsts' => $hsts,
            ]
        );
        if (count($rewrites) > 0) {
            $middleware[] = new RewriteMiddleware($rewrites);
        }
        if ($public !== null && file_exists($public) && is_dir($public)) {
            $middleware[] = new WebrootPreloadMiddleware(
                $public,
                new ContextLogger($logger, ['section' => 'webroot'], 'webroot')
            );
        }
        array_push($middleware, ...$middlwareSuffix);
        $middleware[] = $controllerMiddleware;
        $middleware[] = $requestHandlerMiddleware;

        return new HttpServer($loop, $logger, $socket, $middleware);
    })
    ->parameter('socket', \DI\get('internal.http-server.socket'))
    ->parameter('public', \DI\get('config.http-server.public'))
    ->parameter('hsts', \DI\get('config.http-server.hsts'))
    ->parameter('middlwarePrefix', \DI\get('config.http-server.middleware.prefix'))
    ->parameter('middlwareSuffix', \DI\get('config.http-server.middleware.suffix'))
    ->parameter('rewrites', \DI\get('config.http-server.rewrites')),
];
