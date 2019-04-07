<?php declare(strict_types=1);

namespace ReactiveApps\Tests\Command\HttpServer\Middleware;

use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use React\Socket\ServerInterface;
use ReactiveApps\Command\HttpServer\Command\HttpServer;
use ReactiveApps\Command\HttpServer\Listener\Shutdown;
use ReactiveApps\Command\HttpServer\Middleware\RequestHandlerMiddleware;
use RingCentral\Psr7\Request;
use RingCentral\Psr7\ServerRequest;
use WyriHaximus\TestUtilities\TestCase;

/**
 * @internal
 */
final class RequestHandlerMiddlewareTest extends TestCase
{
    public function testRequestHandling()
    {
        $handlerCalled = false;
        $request = (new ServerRequest(
            'GET',
            'https://example.com/'
        ))->withAttribute('request-handler', function () use (&$handlerCalled) {
            $handlerCalled = true;
        });

        (new RequestHandlerMiddleware())($request);

        self::assertTrue($handlerCalled);
    }
}
