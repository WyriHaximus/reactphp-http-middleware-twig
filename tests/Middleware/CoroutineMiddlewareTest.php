<?php declare(strict_types=1);

namespace ReactiveApps\Tests\Command\HttpServer\Middleware;

use Prophecy\Argument;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use React\EventLoop\Factory;
use function React\Promise\resolve;
use ReactiveApps\Command\HttpServer\Middleware\CoroutineMiddleware;
use Recoil\React\ReactKernel;
use RingCentral\Psr7\Response;
use RingCentral\Psr7\ServerRequest;
use Rx\ObservableInterface;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
use WyriHaximus\Recoil\Call;
use WyriHaximus\Recoil\QueueCallerInterface;
use WyriHaximus\Recoil\State;

/**
 * @internal
 */
final class CoroutineMiddlewareTest extends AsyncTestCase
{
    public function testCoroutine(): void
    {
        $loop = Factory::create();
        $kernel = ReactKernel::create($loop);
        $queueCaller = $this->prophesize(QueueCallerInterface::class);
        $queueCaller->call(Argument::type(ObservableInterface::class))->shouldBeCalled()->will(function ($args) use ($kernel) {
            /** @var ObservableInterface $observable */
            $observable = $args[0];
            $observable->subscribe(function (Call $call) use ($kernel): void {
                $kernel->execute(function () use ($call) {
                    $call->resolve(yield ($call->getCallable())(...$call->getArguments()));
                });
            });

            return new State();
        });

        $container = $this->prophesize(ContainerInterface::class);
        $container->get(RequestHandlerStub::class)->shouldNotBeCalled();

        $request = (new ServerRequest(
            'GET',
            'https://example.com/'
        ))->withAttribute(
            'request-handler',
            RequestHandlerStub::class . '::handlerCoroutineRequest'
        )->withAttribute(
            'request-handler-annotations',
            ['coroutine' => true]
        )->withAttribute(
            'request-handler-static',
            true
        );

        $shouldNotBeenReached = false;
        /** @var ResponseInterface $response */
        $response = $this->await((new CoroutineMiddleware($queueCaller->reveal(), $container->reveal()))($request, function () use (&$shouldNotBeenReached) {
            $shouldNotBeenReached = true;

            return resolve(new Response(500));
        }), $loop);

        self::assertFalse($shouldNotBeenReached);
        self::assertSame(123, $response->getStatusCode());
    }

    public function testCoroutineContainer(): void
    {
        $loop = Factory::create();
        $kernel = ReactKernel::create($loop);
        $queueCaller = $this->prophesize(QueueCallerInterface::class);
        $queueCaller->call(Argument::type(ObservableInterface::class))->shouldBeCalled()->will(function ($args) use ($kernel) {
            /** @var ObservableInterface $observable */
            $observable = $args[0];
            $observable->subscribe(function (Call $call) use ($kernel): void {
                $kernel->execute(function () use ($call) {
                    $call->resolve(yield ($call->getCallable())(...$call->getArguments()));
                });
            });

            return new State();
        });

        $container = $this->prophesize(ContainerInterface::class);
        $container->get(RequestHandlerStub::class)->shouldBeCalled()->willReturn(new RequestHandlerStub());

        $request = (new ServerRequest(
            'GET',
            'https://example.com/'
        ))->withAttribute(
            'request-handler',
            RequestHandlerStub::class . '::handlerCoroutineRequest'
        )->withAttribute(
            'request-handler-annotations',
            ['coroutine' => true]
        )->withAttribute(
            'request-handler-static',
            false
        );

        $shouldNotBeenReached = false;
        /** @var ResponseInterface $response */
        $response = $this->await((new CoroutineMiddleware($queueCaller->reveal(), $container->reveal()))($request, function () use (&$shouldNotBeenReached) {
            $shouldNotBeenReached = true;

            return resolve(new Response(500));
        }), $loop);

        self::assertFalse($shouldNotBeenReached);
        self::assertSame(123, $response->getStatusCode());
    }

    public function testNotACoroutineRequest(): void
    {
        $queueCaller = $this->prophesize(QueueCallerInterface::class);
        $queueCaller->call(Argument::type(ObservableInterface::class))->shouldBeCalled()->willReturn(new State());

        $container = $this->prophesize(ContainerInterface::class);

        $handlerCalled = false;
        $request = (new ServerRequest(
            'GET',
            'https://example.com/'
        ))->withAttribute('request-handler-annotations', ['coroutine' => false]);

        (new CoroutineMiddleware($queueCaller->reveal(), $container->reveal()))($request, function () use (&$handlerCalled) {
            $handlerCalled = true;

            return new Response();
        });

        self::assertTrue($handlerCalled);
    }
}
