<?php declare(strict_types=1);

namespace ReactiveApps\Command\HttpServer;

use React\EventLoop\LoopInterface;
use React\Http\Response;
use React\Http\Server as ReactHttpServer;
use React\Socket\Server as SocketServer;
use ReactiveApps\Command\Command;

final class HttpServer implements Command
{
    const COMMAND = 'http-server';

    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @param LoopInterface $loop
     */
    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    public function __invoke()
    {
        $socket = new SocketServer('0.0.0.0:8888', $this->loop);
        $httpServer = new ReactHttpServer(function () {
            return new Response(200, [], 'Hello World');
        });
        $httpServer->listen($socket);
    }
}
