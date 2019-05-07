<?php declare(strict_types=1);

namespace ReactiveApps\Command\HttpServer\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use React\Promise\Promise;
use React\Promise\PromiseInterface;
use ReactiveApps\Command\HttpServer\RequestHandlerFactory;
use Rx\Subject\Subject;
use WyriHaximus\Recoil\Call;
use WyriHaximus\Recoil\QueueCallerInterface;

final class CoroutineMiddleware
{
    /** @var Subject */
    private $callStream;

    /** @var RequestHandlerFactory */
    private $requestHandlerFactory;

    public function __construct(QueueCallerInterface $queueCaller, RequestHandlerFactory $requestHandlerFactory)
    {
        $this->callStream = new Subject();
        $queueCaller->call($this->callStream);
        $this->requestHandlerFactory = $requestHandlerFactory;
    }

    public function __invoke(ServerRequestInterface $request, callable $next)
    {
        $requestHandlerAnnotations = $request->getAttribute('request-handler-annotations');

        if (isset($requestHandlerAnnotations['coroutine']) && $requestHandlerAnnotations['coroutine'] === true) {
            return $this->runCoroutine($request);
        }

        return $next($request);
    }

    private function runCoroutine(ServerRequestInterface $request): PromiseInterface
    {
        return new Promise(function ($resolve, $reject) use ($request): void {
            $requestHandler = $this->requestHandlerFactory->create($request);
            $call = new Call($requestHandler, $request);
            $call->wait($resolve, $reject);
            $this->callStream->onNext($call);
        });
    }
}
