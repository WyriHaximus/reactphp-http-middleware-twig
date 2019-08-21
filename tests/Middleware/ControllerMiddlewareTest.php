<?php declare(strict_types=1);

namespace ReactiveApps\Tests\Command\HttpServer\Middleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Factory;
use function React\Promise\resolve;
use ReactiveApps\Command\HttpServer\Controller\JWT;
use ReactiveApps\Command\HttpServer\Middleware\ControllerMiddleware;
use RingCentral\Psr7\Response;
use RingCentral\Psr7\ServerRequest;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;

/**
 * @internal
 */
final class ControllerMiddlewareTest extends AsyncTestCase
{
    public function test200(): void
    {
        $loop = Factory::create();

        $container = $this->prophesize(ContainerInterface::class);

        $request = new ServerRequest(
            'GET',
            'https://example.com/thruway/jwt/token.json'
        );

        /** @var ServerRequestInterface|null $passedRequest */
        $passedRequest = null;
        /** @var ResponseInterface $response */
        $response = $this->await(
            (new ControllerMiddleware($container->reveal()))($request, function (ServerRequestInterface $request) use (&$passedRequest) {
                $passedRequest = $request;

                return resolve(new Response(200));
            }),
            $loop
        );

        self::assertInstanceOf(RequestInterface::class, $passedRequest);
        self::assertSame(200, $response->getStatusCode());
        self::assertSame(JWT::class . '::token', $passedRequest->getAttribute('request-handler'));
        self::assertSame([
            'childprocess' => false,
            'coroutine' => false,
            'thread' => false,
        ], $passedRequest->getAttribute('request-handler-annotations'));
        self::assertFalse($passedRequest->getAttribute('request-handler-static'));
    }

    public function test200AlternativeRoute(): void
    {
        $loop = Factory::create();

        $container = $this->prophesize(ContainerInterface::class);

        $request = new ServerRequest(
            'GET',
            'https://example.com/thruway/jwt/default.json'
        );

        /** @var ServerRequestInterface|null $passedRequest */
        $passedRequest = null;
        /** @var ResponseInterface $response */
        $response = $this->await(
            (new ControllerMiddleware($container->reveal()))($request, function (ServerRequestInterface $request) use (&$passedRequest) {
                $passedRequest = $request;

                return resolve(new Response(123));
            }),
            $loop
        );

        self::assertSame(123, $response->getStatusCode());
        self::assertInstanceOf(RequestInterface::class, $passedRequest);
        self::assertSame('default', $passedRequest->getAttribute('realm'));
        self::assertSame(JWT::class . '::token', $passedRequest->getAttribute('request-handler'));
        self::assertSame([
            'childprocess' => false,
            'coroutine' => false,
            'thread' => false,
        ], $passedRequest->getAttribute('request-handler-annotations'));
        self::assertFalse($passedRequest->getAttribute('request-handler-static'));
    }

    public function test404(): void
    {
        $loop = Factory::create();

        $container = $this->prophesize(ContainerInterface::class);

        $request = new ServerRequest(
            'GET',
            'https://example.com/'
        );

        $shouldNotBeenReached = false;
        /** @var ResponseInterface $response */
        $response = $this->await(
            (new ControllerMiddleware($container->reveal()))($request, function () use (&$shouldNotBeenReached) {
                $shouldNotBeenReached = true;

                return resolve(new Response(500));
            }),
            $loop
        );

        self::assertFalse($shouldNotBeenReached);
        self::assertSame(404, $response->getStatusCode());
    }

    public function test405(): void
    {
        $loop = Factory::create();

        $container = $this->prophesize(ContainerInterface::class);

        $request = new ServerRequest(
            'POST',
            'https://example.com/thruway/jwt/token.json'
        );

        $shouldNotBeenReached = false;
        /** @var ResponseInterface $response */
        $response = $this->await(
            (new ControllerMiddleware($container->reveal()))($request, function () use (&$shouldNotBeenReached) {
                $shouldNotBeenReached = true;

                return resolve(new Response(500));
            }),
            $loop
        );

        self::assertFalse($shouldNotBeenReached);
        self::assertSame(405, $response->getStatusCode());
        self::assertSame('GET', $response->getHeaderLine('Allow'));
    }
}
