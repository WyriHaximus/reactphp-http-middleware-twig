<?php declare(strict_types=1);

namespace ReactiveApps\Tests\Command\HttpServer\Middleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use React\EventLoop\Factory;
use function React\Promise\resolve;
use ReactiveApps\Command\HttpServer\Middleware\ControllerMiddleware;
use RingCentral\Psr7\Response;
use RingCentral\Psr7\ServerRequest;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;

/**
 * @internal
 */
final class ControllerMiddlewareTest extends AsyncTestCase
{
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
}
