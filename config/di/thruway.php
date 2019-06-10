<?php declare(strict_types=1);

use Psr\EventDispatcher\EventDispatcherInterface;
use React\EventLoop\LoopInterface;
use ReactiveApps\Command\HttpServer\Event\RealmClose;
use ReactiveApps\Command\HttpServer\Event\RealmOpen;
use Thruway\ClientSession;
use Thruway\Middleware;
use Thruway\Peer\Client;
use Thruway\Peer\Router;

return [
    'internal.http-server.thruway.middleware' => \DI\factory(function (
        LoopInterface $loop,
        EventDispatcherInterface $dispatcher,
        array $realms = []
    ) {
        $realms['heartbeat'] = [];

        $router = new Router($loop);

        foreach ($realms as $realm => $options) {
            $internalClient = new Client($realm, $loop);
            $internalClient->on('open', function (ClientSession $session) use ($realm, $dispatcher): void {
                $dispatcher->dispatch(new RealmOpen($realm, $session));
            });
            $internalClient->on('close', function (ClientSession $session) use ($realm, $dispatcher): void {
                $dispatcher->dispatch(new RealmClose($realm, $session));
            });
            $router->addInternalClient($internalClient);
        }

        $router->start(false);

        return new Middleware(['/'], $loop, $router);
    })
        ->parameter('realms', \DI\get('config.http-server.thruway.realms')),
];
