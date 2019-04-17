<?php declare(strict_types=1);

namespace ReactiveApps\Tests\Command\HttpServer\Middleware;

use Psr\Http\Message\ResponseInterface;
use ReactiveApps\Command\HttpServer\Middleware\TemplateRenderMiddleware;
use RingCentral\Psr7\Response;
use RingCentral\Psr7\ServerRequest;
use WyriHaximus\TestUtilities\TestCase;

/**
 * @internal
 */
final class TemplateRenderMiddlewareTest extends TestCase
{
    public function testCallNextOnNonTemplateResponse(): void
    {
        $request = (new ServerRequest(
            'GET',
            'https://example.com/'
        ));

        /** @var ResponseInterface $response */
        $response = (new TemplateRenderMiddleware())($request, function () {
            return new Response(200, [], 'no-template');
        });

        self::assertSame('no-template', $response->getBody()->getContents());
    }
}
