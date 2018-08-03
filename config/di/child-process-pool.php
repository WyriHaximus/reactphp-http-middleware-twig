<?php

use React\EventLoop\LoopInterface;
use WyriHaximus\React\ChildProcess\Closure\ClosureChild;
use WyriHaximus\React\ChildProcess\Pool\Factory\Flexible;
use WyriHaximus\React\ChildProcess\Pool\Options;
use WyriHaximus\React\ChildProcess\Pool\PoolInterface;
use WyriHaximus\React\Inspector\ChildProcessPools\ChildProcessPoolsCollector;

return [
    'internal.http-server.child-process.pool' => \DI\factory(function (
        LoopInterface $loop,
        ChildProcessPoolsCollector $collector = null
    ) {
        $childProcessPool = Flexible::createFromClass(
            ClosureChild::class,
            $loop,
            [
                Options::TTL      => 0.25,
                Options::MIN_SIZE => 0,
                Options::MAX_SIZE => 5,
            ]
        );

        if ($collector instanceof ChildProcessPoolsCollector) {
            $childProcessPool->done(function (PoolInterface $childProcessPool) use ($collector) {
                $collector->register('http-server', $childProcessPool);
            });
        }

        return $childProcessPool;
    }),
];
