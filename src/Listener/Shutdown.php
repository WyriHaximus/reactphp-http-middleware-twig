<?php declare(strict_types=1);

namespace ReactiveApps\Command\HttpServer\Listener;

use Cake\Collection\Collection;
use Doctrine\Common\Annotations\AnnotationReader;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use React\Socket\Server;
use ReactiveApps\Command\HttpServer\Annotations\Method;
use ReactiveApps\Command\HttpServer\Annotations\Route;
use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflector\ClassReflector;
use Roave\BetterReflection\SourceLocator\Type\SingleFileSourceLocator;
use function FastRoute\simpleDispatcher;
use function WyriHaximus\from_get_in_packages_composer;
use function WyriHaximus\toChildProcessOrNotToChildProcess;
use function WyriHaximus\toCoroutineOrNotToCoroutine;

final class Shutdown
{
    /** @var Server */
    private $socket;

    /** @var LoggerInterface */
    private $logger;

    /**
     * @param Server $socket
     * @param LoggerInterface $logger
     */
    public function __construct(Server $socket, LoggerInterface $logger)
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
