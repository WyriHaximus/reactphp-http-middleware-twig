<?php declare(strict_types=1);

namespace ReactiveApps\Tests\Command\HttpServer\Command;

use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use React\EventLoop\Factory;
use React\Socket\ServerInterface;
use ReactiveApps\Command\HttpServer\Command\HttpServer;
use ReactiveApps\LifeCycleEvents\Promise\Shutdown;
use Recoil\React\ReactKernel;
use WyriHaximus\TestUtilities\TestCase;

/**
 * @internal
 */
final class HttpServerTest extends TestCase
{
    public function testHttpServer(): void
    {
        $loop = Factory::create();
        $kernel = ReactKernel::create($loop);

        $logger = $this->prophesize(LoggerInterface::class);
        $logger->debug('Creating HTTP server')->shouldBeCalled();
        $logger->debug('Creating HTTP server socket')->shouldBeCalled();
        $logger->debug('Listening for incoming requests')->shouldBeCalled();

        $socket = $this->prophesize(ServerInterface::class);
        $socket->on('connection', Argument::type('array'))->shouldBeCalled();

        $shutdown = new Shutdown();

        $middleware = [];

        $httpServer = new HttpServer($logger->reveal(), $socket->reveal(), $middleware, $shutdown);

        $kernel->execute(function () use ($httpServer) {
            yield $httpServer();
        });

        $loop->futureTick(function () use ($shutdown) {
            $shutdown();
        });

        $loop->run();
    }
}
