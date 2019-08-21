<?php declare(strict_types=1);

namespace ReactiveApps\Command\HttpServer\Listener;

use ReactiveApps\Command\HttpServer\Event\RealmClose as RealmCloseEvent;

final class RealmClose
{
    public function __invoke(RealmCloseEvent $event): void
    {
        foreach ($event->getRealm()->getRpcs() as $rpc) {
            $event->getSession()->unregister($rpc->getName());
        }
    }
}
