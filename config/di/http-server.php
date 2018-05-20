<?php

use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use ReactiveApps\Command\HttpServer\HttpServer;
use ReactiveApps\Rx\Shutdown;

return [
    HttpServer::class => \DI\factory(function (LoopInterface $loop, LoggerInterface $logger, Shutdown $shutdown, string $address, callable $handler, string $public = null) {
        return new HttpServer($loop, $logger, $shutdown, $address, $handler, $public);
    })
    ->parameter('address', \DI\get('config.http-server.address'))
    ->parameter('handler', \DI\get('config.http-server.handler'))
    ->parameter('public', \DI\get('config.http-server.public')),
];
