<?php declare(strict_types=1);

namespace ReactiveApps\Command\HttpServer\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use function React\Promise\resolve;
use ReactiveApps\Command\HttpServer\TemplateResponse;
use function RingCentral\Psr7\stream_for;
use Twig\Environment;

final class TemplateRenderMiddleware
{
    /** @var Environment */
    private $twig;

    /**
     * TemplateRenderMiddleware constructor.
     * @param Environment $twig
     */
    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function __invoke(ServerRequestInterface $request, $next)
    {
        $template = $request->getAttribute('request-handler-template', false);

        if ($template === false) {
            return $next($request);
        }

        return resolve($next($request))->then(function (ResponseInterface $response) use ($template) {
            if ($response instanceof TemplateResponse) {
                $response = $response->withBody(
                    stream_for(
                        $this->twig->render(
                            $template,
                            $response->getTemplateData()
                        )
                    )
                );
            }

            return $response;
        });
    }
}
