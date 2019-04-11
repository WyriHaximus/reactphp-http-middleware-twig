<?php declare(strict_types=1);

namespace ReactiveApps\Command\HttpServer\Command;

use Psr\Log\LoggerInterface;
use React\Http\StreamingServer as ReactHttpServer;
use React\Socket\ServerInterface as SocketServerInterface;
use ReactiveApps\Command\Command;
use WyriHaximus\PSR3\CallableThrowableLogger\CallableThrowableLogger;

final class HttpServer implements Command
{
    const COMMAND = 'http-server';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SocketServerInterface
     */
    private $socket;

    /**
     * @var array
     */
    private $middleware;

    /**
     * @param LoggerInterface       $logger
     * @param SocketServerInterface $socket
     * @param array                 $middleware
     */
    public function __construct(LoggerInterface $logger, SocketServerInterface $socket, array $middleware)
    {
        $this->logger = $logger;
        $this->socket = $socket;
        $this->middleware = $middleware;
    }

    public function __invoke(): void
    {
        $this->logger->debug('Creating HTTP server');
        $httpServer = new ReactHttpServer($this->middleware);
        $httpServer->on('error', CallableThrowableLogger::create($this->logger));

        $this->logger->debug('Creating HTTP server socket');
        $httpServer->listen($this->socket);
        $this->logger->debug('Listening for incoming requests');
    }
}
