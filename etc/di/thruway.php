<?php declare(strict_types=1);

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use ReactiveApps\Command\HttpServer\Event\RealmClose;
use ReactiveApps\Command\HttpServer\Event\RealmOpen;
use ReactiveApps\Command\HttpServer\Rpc\Heartbeat;
use ReactiveApps\Command\HttpServer\Thruway\Realm;
use ReactiveApps\Command\HttpServer\Thruway\RealmAuth;
use ReactiveApps\Command\HttpServer\Thruway\Rpc;
use Thruway\ClientSession;
use Thruway\Logging\Logger;
use Thruway\Middleware;
use Thruway\Peer\Client;
use Thruway\Peer\Router;
use WoWScreenshotsNet\Controller\JWT;

return [
    'internal.http-server.thruway.middleware' => \DI\factory(function (
        LoopInterface $loop,
        LoggerInterface $logger,
        EventDispatcherInterface $dispatcher,
        array $realms = []
    ) {
        Logger::set($logger);

        $router = new Router($loop);

        /** @var Realm $realm */
        foreach ($realms as $realm) {
            $internalClient = new Client($realm->getName(), $loop);
            $internalClient->on('open', function (ClientSession $session) use ($realm, $dispatcher): void {
                $dispatcher->dispatch(new RealmOpen($realm, $session));
            });
            $internalClient->on('close', function (ClientSession $session) use ($realm, $dispatcher): void {
                $dispatcher->dispatch(new RealmClose($realm, $session));
            });
            $router->addInternalClient($internalClient);
        }

        $router->start(false);

        return new Middleware(['/', '/ws/'], $loop, $router);
    })
        ->parameter('realms', \DI\get('internal.http-server.thruway.realms')),
    JWT::class => \DI\factory(function (
        array $realms = []
    ) {
        return new JWT($realms);
    })
        ->parameter('realms', \DI\get('internal.http-server.thruway.realms')),
    'internal.http-server.thruway.realms' => \DI\factory(function (
        array $realms = []
    ) {
        $realms[] = new Realm(
            'heartbeat',
            new RealmAuth(false, ''),
            [
                new Rpc('heartbeat', Heartbeat::class),
            ]
        );

        $realms = \array_values($realms);

        $indexRealms = [];
        /** @var Realm $realm */
        foreach ($realms as $realm) {
            $indexRealms[$realm->getName()] = $realm;
        }

        return $indexRealms;
    })
        ->parameter('realms', \DI\get('config.http-server.thruway.realms')),
];
