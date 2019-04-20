<?php declare(strict_types=1);

namespace ReactiveApps\Tests\Command\HttpServer\Middleware;

use Psr\Http\Message\ResponseInterface;
use ReactiveApps\Command\HttpServer\Middleware\TemplateRenderMiddleware;
use ReactiveApps\Command\HttpServer\TemplateResponse;
use RingCentral\Psr7\Response;
use RingCentral\Psr7\ServerRequest;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;

/**
 * @internal
 */
final class TemplateRenderMiddlewareTest extends AsyncTestCase
{
    public function testRenderOnTemplateResponse(): void
    {
        $request = (new ServerRequest(
            'GET',
            'https://example.com/'
        ))->withAttribute('request-handler-template', 'template_pawufhuiwfe');

        /** @var ResponseInterface $response */
        $response = $this->await((new TemplateRenderMiddleware(new Environment(
            new ArrayLoader([
                'template_pawufhuiwfe.twig' => 'Beer from a {{ foo }}',
            ])
        )))($request, function () {
            return (new TemplateResponse())->withTemplateData(['foo' => 'bar']);
        }));

        self::assertSame('Beer from a bar', $response->getBody()->getContents());
    }

    public function testCallNextOnNonTemplateResponse(): void
    {
        $request = (new ServerRequest(
            'GET',
            'https://example.com/'
        ));

        /** @var ResponseInterface $response */
        $response = (new TemplateRenderMiddleware(new Environment(
            new ArrayLoader([])
        )))($request, function () {
            return new Response(200, [], 'no-template');
        });

        self::assertSame('no-template', $response->getBody()->getContents());
    }
}
