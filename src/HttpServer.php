<?php declare(strict_types=1);

namespace ReactiveApps\Command\HttpServer;

use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
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
     * @var string
     */
    private $address;

    /**
     * @var callable
     */
    private $handler;

    /**
     * @param LoopInterface $loop
     * @param LoggerInterface $logger
     * @param string $address
     * @param callable $handler
     */
    public function __construct(LoopInterface $loop, LoggerInterface $logger, string $address, callable $handler)
    {
        $this->loop = $loop;
        $this->logger = $logger;
        $this->address = $address;
        $this->handler = $handler;
    }

    public function __invoke()
    {
        $socket = new SocketServer($this->address, $this->loop);
        $middleware = [];
        $middleware[] = Factory::create($this->loop, $this->logger);
        $middleware[] = $this->handler;
        $httpServer = new ReactHttpServer($middleware);
        $httpServer->listen($socket);
    }
}
