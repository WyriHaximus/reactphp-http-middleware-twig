<?php declare(strict_types=1);

namespace ReactiveApps\Command\HttpServer\Command;

use Generator;
use Psr\Log\LoggerInterface;
use React\Http\StreamingServer as ReactHttpServer;
use React\Socket\ServerInterface as SocketServerInterface;
use ReactiveApps\Command\Command;
use ReactiveApps\LifeCycleEvents\Promise\Shutdown;
use WyriHaximus\Annotations\Coroutine;
use WyriHaximus\PSR3\CallableThrowableLogger\CallableThrowableLogger;

/**
 * @Coroutine()
 */
final class HttpServer implements Command
{
    const COMMAND = 'http-server';

    /** @var LoggerInterface */
    private $logger;

    /** @var SocketServerInterface */
    private $socket;

    /** @var callable[] */
    private $middleware;

    /** @var Shutdown */
    private $shutdownEventPromise;

    /**
     * @param LoggerInterface       $logger
     * @param SocketServerInterface $socket
     * @param callable[]            $middleware
     * @param Shutdown              $shutdownEventPromise
     */
    public function __construct(LoggerInterface $logger, SocketServerInterface $socket, array $middleware, Shutdown $shutdownEventPromise)
    {
        $this->logger = $logger;
        $this->socket = $socket;
        $this->middleware = $middleware;
        $this->shutdownEventPromise = $shutdownEventPromise;
    }

    public function __invoke(): Generator
    {
        $this->logger->debug('Creating HTTP server');
        $httpServer = new ReactHttpServer($this->middleware);
        $httpServer->on('error', CallableThrowableLogger::create($this->logger));

        $this->logger->debug('Creating HTTP server socket');
        $httpServer->listen($this->socket);
        $this->logger->debug('Listening for incoming requests');

        yield $this->shutdownEventPromise;

        return 0;
    }
}
