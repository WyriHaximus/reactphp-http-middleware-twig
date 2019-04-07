<?php declare(strict_types=1);

namespace ReactiveApps\Command\HttpServer\Middleware;

use Psr\Http\Message\ServerRequestInterface;

final class RequestHandlerMiddleware
{
    public function __invoke(ServerRequestInterface $request)
    {
        return ($request->getAttribute('request-handler'))($request);
    }
}
