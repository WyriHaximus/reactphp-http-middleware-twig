<?php declare(strict_types=1);

namespace ReactiveApps\Tests\Command\HttpServer\Middleware;

use Prophecy\Argument;
use function React\Promise\resolve;
use ReactiveApps\Command\HttpServer\Middleware\ThreadMiddleware;
use RingCentral\Psr7\Response;
use RingCentral\Psr7\ServerRequest;
use WyriHaximus\React\Parallel\PoolInterface;
use WyriHaximus\TestUtilities\TestCase;

/**
 * @internal
 */
final class ThreadMiddlewareTest extends TestCase
{
    public function testRequestHandling(): void
    {
        $pool = $this->prophesize(PoolInterface::class);
        $pool->run(Argument::type('callable'), Argument::type('array'))->shouldBeCalled()->will(function ($args) {
            [$callable, $arguments] = $args;

            return resolve($callable(...$arguments));
        });

        $handlerCalled = false;
        $request = (new ServerRequest(
            'GET',
            'https://example.com/'
        ))->withAttribute('request-handler', function () use (&$handlerCalled) {
            $handlerCalled = true;

            return new Response();
        })->withAttribute('request-handler-annotations', ['thread' => true]);

        (new ThreadMiddleware($pool->reveal()))($request, function (): void {
        });

        self::assertTrue($handlerCalled);
    }

    public function testNotAThreadingRequest(): void
    {
        $pool = $this->prophesize(PoolInterface::class);
        $pool->run(Argument::type('callable'), Argument::type('array'))->shouldNotBeCalled();

        $handlerCalled = false;
        $request = (new ServerRequest(
            'GET',
            'https://example.com/'
        ))->withAttribute('request-handler-annotations', ['thread' => false]);

        (new ThreadMiddleware($pool->reveal()))($request, function () use (&$handlerCalled) {
            $handlerCalled = true;

            return new Response();
        });

        self::assertTrue($handlerCalled);
    }
}
