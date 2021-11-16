<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use RingCentral\Psr7\Response;
use RingCentral\Psr7\ServerRequest;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
use WyriHaximus\React\Http\Middleware\TemplateRenderMiddleware;
use WyriHaximus\React\Http\Middleware\TemplateResponse;

use function assert;

/**
 * @internal
 */
final class TemplateRenderMiddlewareTest extends AsyncTestCase
{
    public function testRenderOnTemplateResponse(): void
    {
        $request = new ServerRequest(
            'GET',
            'https://example.com/'
        );

        $response = $this->await((new TemplateRenderMiddleware(new Environment(
            new ArrayLoader(['template_pawufhuiwfe' => 'Beer from a {{ foo }}'])
        )))($request, static function (): ResponseInterface {
            return (new TemplateResponse())->withTemplateData(['foo' => 'bar'])->withTemplate('template_pawufhuiwfe');
        }));
        assert($response instanceof ResponseInterface);

        self::assertSame('Beer from a bar', $response->getBody()->getContents());
    }

    public function testCallNextOnNonTemplateResponse(): void
    {
        $request = new ServerRequest(
            'GET',
            'https://example.com/'
        );

        $response = $this->await((new TemplateRenderMiddleware(new Environment(
            new ArrayLoader([])
        )))($request, static function (): ResponseInterface {
            return new Response(200, [], 'no-template');
        }));
        assert($response instanceof ResponseInterface);

        self::assertSame('no-template', $response->getBody()->getContents());
    }
}
