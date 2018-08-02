<?php declare(strict_types=1);

namespace ReactiveApps\Command\HttpServer;

use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\LoopInterface;
use React\Promise\Promise;
use React\Promise\PromiseInterface;
use Recoil\React\ReactKernel;
use Rx\Subject\Subject;
use WyriHaximus\React\ChildProcess\Closure\MessageFactory;
use WyriHaximus\React\ChildProcess\Messenger\Messages\Payload;
use WyriHaximus\React\ChildProcess\Pool\PoolInterface;
use WyriHaximus\Recoil\Call;
use WyriHaximus\Recoil\QueueCaller;
use function WyriHaximus\psr7_response_decode;
use function WyriHaximus\psr7_response_encode;
use function WyriHaximus\psr7_server_request_decode;
use function WyriHaximus\psr7_server_request_encode;

final class RequestHandlerMiddleware
{
    /**
     * @var Subject
     */
    private $callStream;

    /**
     * @var PromiseInterface
     */
    private $pool;

    public function __construct(LoopInterface $loop, PromiseInterface $pool)
    {
        $this->callStream = new Subject();
        (new QueueCaller(ReactKernel::create($loop)))->call($this->callStream);
        $this->pool = $pool;
    }

    public function __invoke(ServerRequestInterface $request)
    {
        $requestHandlerAnnotations = $request->getAttribute('request-handler-annotations');

        if (isset($requestHandlerAnnotations['coroutine']) && $requestHandlerAnnotations['coroutine'] === true) {
            return $this->runCoroutine($request);
        }

        if (isset($requestHandlerAnnotations['childprocess']) && $requestHandlerAnnotations['childprocess'] === true) {
            return $this->runChildProcess($request);
        }

        $requestHandler = $request->getAttribute('request-handler');
        return $requestHandler($request);
    }

    private function runCoroutine(ServerRequestInterface $request): PromiseInterface
    {
        return new Promise(function ($resolve, $reject) use ($request) {
            $call = new Call($request->getAttribute('request-handler'), $request);
            $call->wait($resolve, $reject);
            $this->callStream->onNext($call);
        });
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

    private function createChildProcessClosure(array $jsonRequest): callable
    {
        return function () use ($jsonRequest) {
            $request = psr7_server_request_decode($jsonRequest);
            $requestHandler = $request->getAttribute('request-handler');
            $response = $requestHandler($request);

            return psr7_response_encode($response);
        };
    }
}
