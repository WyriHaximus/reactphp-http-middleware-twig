<?php declare(strict_types=1);

namespace ReactiveApps\Tests\Command\HttpServer\Listener;

use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use React\Socket\ServerInterface;
use ReactiveApps\Command\HttpServer\Command\HttpServer;
use ReactiveApps\Command\HttpServer\Listener\Shutdown;
use WyriHaximus\TestUtilities\TestCase;

/**
 * @internal
 */
final class ShutdownTest extends TestCase
{
    public function testShutdown()
    {
        $logger = $this->prophesize(LoggerInterface::class);
        $logger->debug('Closed listening socket for new incoming requests')->shouldBeCalled();

        $socket = $this->prophesize(ServerInterface::class);
        $socket->close()->shouldBeCalled();

        $httpServer = new Shutdown($socket->reveal(), $logger->reveal());
        $httpServer();
    }
}
