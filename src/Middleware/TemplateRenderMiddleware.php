<?php declare(strict_types=1);

namespace ReactiveApps\Command\HttpServer\Middleware;

use Psr\Http\Message\ServerRequestInterface;

final class TemplateRenderMiddleware
{
    public function __invoke(ServerRequestInterface $request, $next)
    {
        return $next($request);
    }
}
