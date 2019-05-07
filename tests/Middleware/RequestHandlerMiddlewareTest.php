<?php declare(strict_types=1);

namespace ReactiveApps\Tests\Command\HttpServer\Middleware;

use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use ReactiveApps\Command\HttpServer\Middleware\RequestHandlerMiddleware;
use ReactiveApps\Command\HttpServer\RequestHandlerFactory;
use ReactiveApps\Tests\Command\HttpServer\RequestHandlerStub;
use RingCentral\Psr7\ServerRequest;
use WyriHaximus\TestUtilities\TestCase;

/**
 * @internal
 */
final class RequestHandlerMiddlewareTest extends TestCase
{
    /** @var ObjectProphecy|ContainerInterface */
    private $container;

    /** @var RequestHandlerStub */
    private $requestHandlerStub;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = $this->prophesize(ContainerInterface::class);

        $this->requestHandlerStub = new RequestHandlerStub();

        $this->container->get(RequestHandlerStub::class)->willReturn($this->requestHandlerStub);
        RequestHandlerStub::resetStaticHandlerCalled();
    }

    public function testStaticRequestHandling(): void
    {
        $request = (new ServerRequest(
            'GET',
            'https://example.com/'
        ))->withAttribute('request-handler', RequestHandlerStub::class . '::methodStatic');

        $shouldNeverBeCalled = false;
        (new RequestHandlerMiddleware(
            new RequestHandlerFactory($this->container->reveal())
        ))($request);

        self::assertTrue(RequestHandlerStub::getStaticHandlerCalled());
        self::assertFalse($shouldNeverBeCalled);
    }

    public function testInstancedRequestHandling(): void
    {
        $request = (new ServerRequest(
            'GET',
            'https://example.com/'
        ))->withAttribute('request-handler', RequestHandlerStub::class . '::method');

        $shouldNeverBeCalled = false;
        (new RequestHandlerMiddleware(
            new RequestHandlerFactory($this->container->reveal())
        ))($request);

        self::assertTrue($this->requestHandlerStub->isHandlerCalled());
        self::assertFalse($shouldNeverBeCalled);
    }
}
