<?php declare(strict_types=1);

namespace ReactiveApps\Tests\Command\HttpServer\Middleware;

use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;
use function React\Promise\resolve;
use ReactiveApps\Command\HttpServer\Middleware\ChildProcessMiddleware;
use RingCentral\Psr7\Response;
use RingCentral\Psr7\ServerRequest;
use SuperClosure\Serializer;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Rpc;
use WyriHaximus\React\ChildProcess\Pool\PoolInterface;

/**
 * @internal
 */
final class ChildProcessMiddlewareTest extends AsyncTestCase
{
    public function testRequestHandling(): void
    {
        $pool = $this->prophesize(PoolInterface::class);
        $pool->rpc(Argument::type(Rpc::class))->shouldBeCalled()->will(function ($args) {
            /** @var Rpc $rpc */
            $rpc = $args[0];

            return new Payload((new Serializer())->unserialize($rpc->getPayload()['closure'])());
        });

        $request = (new ServerRequest(
            'GET',
            'https://example.com/'
        ))->withAttribute(
            'request-handler',
            RequestHandlerStub::class . '::handlerRequest'
        )->withAttribute(
            'request-handler-annotations',
            ['childprocess' => true]
        );

        /** @var ResponseInterface $response */
        $response = $this->await((new ChildProcessMiddleware(resolve($pool->reveal())))($request, function (): void {
        }));

        self::assertSame(123, $response->getStatusCode());
    }

    public function testNotAChildProcessingRequest(): void
    {
        $pool = $this->prophesize(PoolInterface::class);
        $pool->rpc(Argument::type(Rpc::class))->shouldNotBeCalled();

        $handlerCalled = false;
        $request = (new ServerRequest(
            'GET',
            'https://example.com/'
        ))->withAttribute('request-handler-annotations', ['childprocess' => false]);

        (new ChildProcessMiddleware(resolve($pool->reveal())))($request, function () use (&$handlerCalled) {
            $handlerCalled = true;

            return new Response();
        });

        self::assertTrue($handlerCalled);
    }
}
