<?php

use ReactiveApps\Command\HttpServer\HttpServer;

return [
    HttpServer::class => \DI\create(HttpServer::class)
        ->parameter('address', \DI\get('config.http-server.address'))
        ->parameter('handler', \DI\get('config.http-server.handler')),
];
