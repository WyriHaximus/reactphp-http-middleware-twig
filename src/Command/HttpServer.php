<?php declare(strict_types=1);

namespace ReactiveApps\Command\HttpServer\Command;

use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use React\Http\Server as ReactHttpServer;
use React\Socket\Server as SocketServer;
use ReactiveApps\Command\Command;
use WyriHaximus\PSR3\CallableThrowableLogger\CallableThrowableLogger;

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
     * @var SocketServer
     */
    private $socket;

    /**
     * @var array
     */
    private $middleware;

    /**
     * @param LoopInterface $loop
     * @param LoggerInterface $logger
     * @param SocketServer $socket
     * @param array $middleware
     */
    public function __construct(LoopInterface $loop, LoggerInterface $logger, SocketServer $socket, array $middleware)
    {
        $this->loop = $loop;
        $this->logger = $logger;
        $this->socket = $socket;
        $this->middleware = $middleware;
    }

    public function __invoke()
    {
        $this->logger->debug('Creating HTTP server');
        $httpServer = new ReactHttpServer($this->middleware);
        $httpServer->on('error', CallableThrowableLogger::create($this->logger));

        $this->logger->debug('Creating HTTP server socket');
        $httpServer->listen($this->socket);
        $this->logger->debug('Listening for incoming requests');
    }
}
