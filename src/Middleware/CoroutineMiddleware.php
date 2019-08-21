<?php declare(strict_types=1);

namespace ReactiveApps\Command\HttpServer\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use React\Promise\PromiseInterface;
use function React\Promise\resolve;
use ReactiveApps\Command\HttpServer\RequestHandlerFactory;
use WyriHaximus\Recoil\PromiseCoroutineWrapper;
use WyriHaximus\Recoil\QueueCallerInterface;

/**
 * @internal
 */
final class CoroutineMiddleware
{
    /** @var PromiseCoroutineWrapper */
    private $wrapper;

    /** @var RequestHandlerFactory */
    private $requestHandlerFactory;

    public function __construct(QueueCallerInterface $queueCaller, RequestHandlerFactory $requestHandlerFactory)
    {
        $this->wrapper = PromiseCoroutineWrapper::createFromQueueCaller($queueCaller);
        $this->requestHandlerFactory = $requestHandlerFactory;
    }

    public function __invoke(ServerRequestInterface $request, callable $next): PromiseInterface
    {
        $requestHandlerAnnotations = $request->getAttribute('request-handler-annotations');

        if (array_key_exists('coroutine', $requestHandlerAnnotations) && $requestHandlerAnnotations['coroutine'] === true) {
            return $this->runCoroutine($request);
        }

        return resolve($next($request));
    }

    private function runCoroutine(ServerRequestInterface $request): PromiseInterface
    {
        $requestHandler = $this->requestHandlerFactory->create($request);

        return $this->wrapper->call($requestHandler, $request);
    }
}
