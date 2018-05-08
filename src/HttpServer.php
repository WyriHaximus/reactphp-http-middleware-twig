<?php declare(strict_types=1);

namespace ReactiveApps\Command\HttpServer;

use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use React\Http\Response;
use React\Http\Server as ReactHttpServer;
use React\Socket\Server as SocketServer;
use ReactiveApps\Command\Command;
use WyriHaximus\React\Http\PSR15MiddlewareGroup\Factory;

final class HttpServer implements Command
{
    const COMMAND = 'http-server';

    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoopInterface $loop
     * @param LoggerInterface $logger
     */
    public function __construct(LoopInterface $loop, LoggerInterface $logger)
    {
        $this->loop = $loop;
        $this->logger = $logger;
    }

    public function __invoke()
    {
        $socket = new SocketServer('0.0.0.0:8888', $this->loop);
        $httpServer = new ReactHttpServer([
            Factory::create($this->loop, $this->logger),
            function () {
                return new Response(200, [], 'Hello World');
            }
        ]);
        $httpServer->listen($socket);
    }
}
