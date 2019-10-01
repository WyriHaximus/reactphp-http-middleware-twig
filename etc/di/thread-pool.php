<?php declare(strict_types=1);

use React\EventLoop\LoopInterface;
use WyriHaximus\React\Inspector\ParallelPools\ParallelPoolsCollector;
use WyriHaximus\React\Parallel\Finite;

return [
    'internal.http-server.thread.pool' => \DI\factory(function (
        LoopInterface $loop,
        ParallelPoolsCollector $collector = null
    ) {
        $pool = Finite::create($loop, 32);

        if ($collector instanceof ParallelPoolsCollector) {
            $collector->register('http-server', $pool);
        }

        return $pool;
    }),
];
