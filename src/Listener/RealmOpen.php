<?php declare(strict_types=1);

namespace ReactiveApps\Command\HttpServer\Listener;

use Psr\Container\ContainerInterface;
use React\Promise\PromiseInterface;
use function React\Promise\resolve;
use ReactiveApps\Command\HttpServer\Event\RealmOpen as RealmOpenEvent;

final class RealmOpen
{
    /** @var ContainerInterface */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(RealmOpenEvent $event): void
    {
        foreach ($event->getRealm()->getRpcs() as $rpc) {
            $handler = $this->container->get($rpc->getCommand());
            $event->getSession()->register($rpc->getName(), function (array $args) use ($handler): PromiseInterface {
                return resolve($handler(...$args));
            });
        }
    }
}
