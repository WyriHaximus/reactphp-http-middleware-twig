<?php

use React\EventLoop\LoopInterface;
use WyriHaximus\React\ChildProcess\Closure\ClosureChild;
use WyriHaximus\React\ChildProcess\Pool\Factory\Flexible;
use WyriHaximus\React\ChildProcess\Pool\Options;

return [
    'internal.http-server.child-process.pool' => \DI\factory(function (
        LoopInterface $loop
    ) {
        return Flexible::createFromClass(
            ClosureChild::class,
            $loop,
            [Options::TTL => 0.25, Options::MIN_SIZE => 0, Options::MAX_SIZE => 5]
        );
    }),
];
