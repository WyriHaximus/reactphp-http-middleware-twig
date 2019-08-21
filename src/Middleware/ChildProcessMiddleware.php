<?php declare(strict_types=1);

namespace ReactiveApps\Command\HttpServer\Middleware;

use Closure;
use Psr\Http\Message\ServerRequestInterface;
use React\Promise\PromiseInterface;
use function React\Promise\resolve;
use function WyriHaximus\psr7_response_decode;
use function WyriHaximus\psr7_response_encode;
use function WyriHaximus\psr7_server_request_decode;
use function WyriHaximus\psr7_server_request_encode;
use WyriHaximus\React\ChildProcess\Closure\MessageFactory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Pool\PoolInterface;

/**
 * @internal
 */
final class ChildProcessMiddleware
{
    /**
     * @var PromiseInterface
     */
    private $pool;

    public function __construct(PromiseInterface $pool)
    {
        $this->pool = $pool;
    }

    public function __invoke(ServerRequestInterface $request, callable $next): PromiseInterface
    {
        $requestHandlerAnnotations = $request->getAttribute('request-handler-annotations');

        if (isset($requestHandlerAnnotations['childprocess']) && $requestHandlerAnnotations['childprocess'] === true) {
            return $this->runChildProcess($request);
        }

        return resolve($next($request));
    }

    private function runChildProcess(ServerRequestInterface $request): PromiseInterface
    {
        $jsonRequest = psr7_server_request_encode($request);
        $rpc = MessageFactory::rpc($this->createChildProcessClosure($jsonRequest));

        return $this->pool->then(function (PoolInterface $pool) use ($rpc) {
            return $pool->rpc($rpc);
        })->then(function (Payload $payload) {
            $response = $payload->getPayload();

            return psr7_response_decode($response);
        });
    }

    /**
     * @codeCoverageIgnore
     */
    private function createChildProcessClosure(array $jsonRequest): Closure
    {
        return function () use ($jsonRequest) {
            $request = psr7_server_request_decode($jsonRequest);
            $requestHandler = $request->getAttribute('request-handler');
            $response = $requestHandler($request);

            return psr7_response_encode($response);
        };
    }
}
