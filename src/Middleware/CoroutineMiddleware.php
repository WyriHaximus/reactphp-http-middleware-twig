<?php declare(strict_types=1);

namespace ReactiveApps\Command\HttpServer\Middleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\Promise\Promise;
use React\Promise\PromiseInterface;
use Rx\Subject\Subject;
use WyriHaximus\Recoil\Call;
use WyriHaximus\Recoil\QueueCallerInterface;

final class CoroutineMiddleware
{
    /**
     * @var Subject
     */
    private $callStream;

    /** @var ContainerInterface */
    private $container;

    public function __construct(QueueCallerInterface $queueCaller, ContainerInterface $container)
    {
        $this->callStream = new Subject();
        $queueCaller->call($this->callStream);
        $this->container = $container;
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
        return new Promise(function ($resolve, $reject) use ($request) {
            $requestHandler = $request->getAttribute('request-handler');
            if ($request->getAttribute('request-handler-static') === false) {
                $requestHandler = (function (string $requestHandler) {
                    [$controller, $method] = \explode('::', $requestHandler);

                    return [
                        $this->container->get($controller),
                        $method,
                    ];
                })($requestHandler);
            }
            $call = new Call($requestHandler, $request);
            $call->wait($resolve, $reject);
            $this->callStream->onNext($call);
        });
    }
}
