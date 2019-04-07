<?php declare(strict_types=1);

namespace ReactiveApps\Command\HttpServer\Listener;

use Psr\Log\LoggerInterface;
use React\Socket\ServerInterface;

final class Shutdown
{
    /** @var ServerInterface */
    private $socket;

    /** @var LoggerInterface */
    private $logger;

    /**
     * @param ServerInterface          $socket
     * @param LoggerInterface $logger
     */
    public function __construct(ServerInterface $socket, LoggerInterface $logger)
    {
        $this->socket = $socket;
        $this->logger = $logger;
    }

    public function __invoke(): void
    {
        $this->socket->close();
        $this->logger->debug('Closed listening socket for new incoming requests');
    }
}
