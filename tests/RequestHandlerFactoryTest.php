<?php declare(strict_types=1);

namespace ReactiveApps\Tests\Command\HttpServer;

use Prophecy\Argument;
use Psr\Container\ContainerInterface;
use React\Http\Io\ServerRequest;
use ReactiveApps\Command\HttpServer\RequestHandlerFactory;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;

/**
 * @internal
 */
final class RequestHandlerFactoryTest extends AsyncTestCase
{
    public function testStaticRequestHandler(): void
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->get(Argument::any())->shouldNotBeCalled();

        $request = (new ServerRequest('GET', 'https://example.com/'))->
            withAttribute('request-handler', RequestHandlerStub::class . '::methodStatic')->
            withAttribute('request-handler-static', true)
        ;

        $factory = new RequestHandlerFactory($container->reveal());

        $requestHandler = $factory->create($request);
        self::assertSame(RequestHandlerStub::class . '::methodStatic', $requestHandler);
    }

    public function testRequestHandler(): void
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->get(RequestHandlerStub::class)->shouldBeCalled()->willReturn(new RequestHandlerStub());

        $request = (new ServerRequest('GET', 'https://example.com/'))->
            withAttribute('request-handler', RequestHandlerStub::class . '::method')->
            withAttribute('request-handler-static', false)
        ;

        $factory = new RequestHandlerFactory($container->reveal());

        $requestHandler = $factory->create($request);
        self::assertIsArray($requestHandler);
        self::assertInstanceOf(RequestHandlerStub::class, $requestHandler[0]);
        self::assertIsString($requestHandler[1]);
        self::assertSame('method', $requestHandler[1]);
    }
}
