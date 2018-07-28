<?php declare(strict_types=1);

namespace ReactiveApps\Command\HttpServer\Command;

use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use React\Http\Server as ReactHttpServer;
use React\Socket\Server as SocketServer;
use ReactiveApps\Command\Command;
use ReactiveApps\Rx\Shutdown;
use WyriHaximus\PSR3\CallableThrowableLogger\CallableThrowableLogger;
use WyriHaximus\PSR3\ContextLogger\ContextLogger;

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
     * @var Shutdown
     */
    private $shutdown;

    /**
     * @var string
     */
    private $address;

    /**
     * @var array
     */
    private $middleware;

    /**
     * @param LoopInterface $loop
     * @param LoggerInterface $logger
     * @param Shutdown $shutdown
     * @param string $address
     * @param array $middleware
     */
    public function __construct(LoopInterface $loop, LoggerInterface $logger, Shutdown $shutdown, string $address, array $middleware)
    {
        $this->loop = $loop;
        $this->logger = new ContextLogger($logger, ['section' => 'http-server'], 'http-server');
        $this->shutdown = $shutdown;
        $this->address = $address;
        $this->middleware = $middleware;
    }

    public function __invoke()
    {
        $this->logger->debug('Creating HTTP server');
        $httpServer = new ReactHttpServer($this->middleware);
        $httpServer->on('error', CallableThrowableLogger::create($this->logger));

        $this->logger->debug('Creating HTTP server socket');
        $socket = new SocketServer($this->address, $this->loop);
        $httpServer->listen($socket);
        $this->logger->debug('Listening for incoming requests');

        // Stop listening and let current requests complete on shutdown
        $this->shutdown->subscribe(null, null, function () use ($socket) {
            $socket->close();
            $this->logger->debug('Closed listening socket for new incoming requests');
        });
    }
}
