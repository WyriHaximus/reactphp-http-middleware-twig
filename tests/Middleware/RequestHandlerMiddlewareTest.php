<?php declare(strict_types=1);

namespace ReactiveApps\Tests\Command\HttpServer\Middleware;

use ReactiveApps\Command\HttpServer\Middleware\RequestHandlerMiddleware;
use RingCentral\Psr7\ServerRequest;
use WyriHaximus\TestUtilities\TestCase;

/**
 * @internal
 */
final class RequestHandlerMiddlewareTest extends TestCase
{
    public function testRequestHandling(): void
    {
        $handlerCalled = false;
        $request = (new ServerRequest(
            'GET',
            'https://example.com/'
        ))->withAttribute('request-handler', function () use (&$handlerCalled): void {
            $handlerCalled = true;
        });

        $shouldNeverBeCalled = false;
        (new RequestHandlerMiddleware())($request, function () use (&$shouldNeverBeCalled): void {
            $shouldNeverBeCalled = true;
        });

        self::assertTrue($handlerCalled);
        self::assertFalse($shouldNeverBeCalled);
    }

    public function testCallNextOnNoHandler(): void
    {
        $request = (new ServerRequest(
            'GET',
            'https://example.com/'
        ));

        $handlerCalled = false;
        (new RequestHandlerMiddleware())($request, function () use (&$handlerCalled): void {
            $handlerCalled = true;
        });

        self::assertTrue($handlerCalled);
    }
}
