<?php

use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use ReactiveApps\Command\HttpServer\HttpServer;
use ReactiveApps\Rx\Shutdown;
use WyriHaximus\PSR3\ContextLogger\ContextLogger;
use WyriHaximus\React\Http\Middleware\WebrootPreloadMiddleware;
use WyriHaximus\React\Http\PSR15MiddlewareGroup\Factory;

return [
    HttpServer::class => \DI\factory(function (LoopInterface $loop, LoggerInterface $logger, Shutdown $shutdown, string $address, callable $handler, string $public = null) {
        $middleware = [];
        $middleware[] = Factory::create($loop, $logger);
        if ($public !== null && file_exists($public) && is_dir($public)) {
            $middleware[] = new WebrootPreloadMiddleware(
                $public,
                new ContextLogger($logger, ['section' => 'webroot'], 'webroot')
            );
        }
        $middleware[] = $handler;

        return new HttpServer($loop, $logger, $shutdown, $address, $middleware);
    })
    ->parameter('address', \DI\get('config.http-server.address'))
    ->parameter('handler', \DI\get('config.http-server.handler'))
    ->parameter('public', \DI\get('config.http-server.public')),
];
