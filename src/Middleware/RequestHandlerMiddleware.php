<?php declare(strict_types=1);

namespace ReactiveApps\Command\HttpServer\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use React\Promise\PromiseInterface;
use function React\Promise\resolve;
use ReactiveApps\Command\HttpServer\RequestHandlerFactory;

/**
 * @internal
 */
final class RequestHandlerMiddleware
{
    /** @var RequestHandlerFactory */
    private $requestHandlerFactory;

    public function __construct(RequestHandlerFactory $requestHandlerFactory)
    {
        $this->requestHandlerFactory = $requestHandlerFactory;
    }

    public function __invoke(ServerRequestInterface $request): PromiseInterface
    {
        return resolve(($this->requestHandlerFactory->create($request))($request));
    }
}
