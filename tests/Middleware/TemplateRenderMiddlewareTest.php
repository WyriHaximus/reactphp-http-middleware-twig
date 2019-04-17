<?php declare(strict_types=1);

namespace ReactiveApps\Tests\Command\HttpServer\Middleware;

use ReactiveApps\Command\HttpServer\Middleware\TemplateRenderMiddleware;
use RingCentral\Psr7\ServerRequest;
use WyriHaximus\TestUtilities\TestCase;

/**
 * @internal
 */
final class TemplateRenderMiddlewareTest extends TestCase
{
    public function testCallNext(): void
    {
        $request = (new ServerRequest(
            'GET',
            'https://example.com/'
        ));

        $handlerCalled = false;
        (new TemplateRenderMiddleware())($request, function () use (&$handlerCalled): void {
            $handlerCalled = true;
        });

        self::assertTrue($handlerCalled);
    }
}
