<?php

declare(strict_types=1);

namespace WyriHaximus\React\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\Promise\PromiseInterface;
use Twig\Environment;

use function React\Promise\resolve;
use function RingCentral\Psr7\stream_for;

/**
 * @internal
 */
final class TemplateRenderMiddleware
{
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function __invoke(ServerRequestInterface $request, callable $next): PromiseInterface
    {
        return resolve($next($request))->then(function (ResponseInterface $response) {
            if ($response instanceof TemplateResponse) {
                $response = $response->withBody(
                    stream_for(
                        $this->twig->render(
                            $response->template(),
                            $response->templateData()
                        )
                    )
                );
            }

            return $response;
        });
    }
}
