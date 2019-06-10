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
    /** @var ObjectProphecy */
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

        /** @var ContainerInterface $container */
        $container = $this->container->reveal();

        (new RequestHandlerMiddleware(
            new RequestHandlerFactory($container)
        ))($request);

        self::assertTrue(RequestHandlerStub::getStaticHandlerCalled());
    }

    public function testInstancedRequestHandling(): void
    {
        $request = (new ServerRequest(
            'GET',
            'https://example.com/'
        ))->withAttribute('request-handler', RequestHandlerStub::class . '::method');

        /** @var ContainerInterface $container */
        $container = $this->container->reveal();

        (new RequestHandlerMiddleware(
            new RequestHandlerFactory($container)
        ))($request);

        self::assertTrue($this->requestHandlerStub->isHandlerCalled());
    }
}
