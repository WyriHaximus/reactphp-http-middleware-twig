<?php declare(strict_types=1);

use React\EventLoop\LoopInterface;
use WyriHaximus\React\ChildProcess\Closure\ClosureChild;
use WyriHaximus\React\ChildProcess\Pool\Factory\Flexible;
use WyriHaximus\React\ChildProcess\Pool\Options;
use WyriHaximus\React\ChildProcess\Pool\PoolInterface;
use WyriHaximus\React\Inspector\ChildProcessPools\ChildProcessPoolsCollector;

return [
    'internal.http-server.child-process.pool' => \DI\factory(function (
        LoopInterface $loop,
        ChildProcessPoolsCollector $collector = null,
        float $ttl = 0.25,
        int $min = 0,
        int $max = 5
    ) {
        $childProcessPool = Flexible::createFromClass(
            ClosureChild::class,
            $loop,
            [
                Options::TTL      => $ttl,
                Options::MIN_SIZE => $min,
                Options::MAX_SIZE => $max,
            ]
        );

        if ($collector instanceof ChildProcessPoolsCollector) {
            $childProcessPool->done(function (PoolInterface $childProcessPool) use ($collector): void {
                $collector->register('http-server', $childProcessPool);
            });
        }

        return $childProcessPool;
    })
        ->parameter('ttl', \DI\get('config.http-server.pool.ttl'))
        ->parameter('min', \DI\get('config.http-server.pool.min'))
        ->parameter('max', \DI\get('config.http-server.pool.max')),
];
