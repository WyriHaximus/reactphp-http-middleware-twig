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

        (new RequestHandlerMiddleware())($request);

        self::assertTrue($handlerCalled);
    }
}
