<?php declare(strict_types=1);

namespace WyriHaximus\React\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\Promise\PromiseInterface;
use function React\Promise\resolve;
use function RingCentral\Psr7\stream_for;
use Twig\Environment;

/**
 * @internal
 */
final class TemplateRenderMiddleware
{
    /** @var Environment */
    private $twig;

    /**
     * @param Environment $twig
     */
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
                            $response->getTemplate(),
                            $response->getTemplateData()
                        )
                    )
                );
            }

            return $response;
        });
    }
}
