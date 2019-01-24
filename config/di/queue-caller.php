<?php declare(strict_types=1);

use React\EventLoop\LoopInterface;
use ReactiveApps\Rx\Shutdown;
use Recoil\Kernel;
use WyriHaximus\React\Inspector\GlobalState;
use WyriHaximus\Recoil\InfiniteCaller;
use WyriHaximus\Recoil\QueueCallerInterface;

return [
    QueueCallerInterface::class => function (Kernel $kernel, LoopInterface $loop, Shutdown $shutdown) {
        $ic = new InfiniteCaller($kernel);

        if (class_exists(GlobalState::class)) {
            $timer = $loop->addPeriodicTimer(0.25, function () use ($ic) {
                foreach ($ic->info() as $key => $value) {
                    GlobalState::set('recoil.pool.' . $key, $value);
                }
            });

            $shutdown->subscribe(null, null, function () use ($loop, $timer) {
                $loop->cancelTimer($timer);
            });
        }
        
        return $ic;
    },
];
