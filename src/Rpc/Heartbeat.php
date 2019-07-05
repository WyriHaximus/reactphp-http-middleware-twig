<?php declare(strict_types=1);

namespace ReactiveApps\Command\HttpServer\Rpc;

use React\Promise\PromiseInterface;
use function React\Promise\resolve;

final class Heartbeat
{
    public function __invoke(): PromiseInterface
    {
        return resolve('pong');
    }
}
