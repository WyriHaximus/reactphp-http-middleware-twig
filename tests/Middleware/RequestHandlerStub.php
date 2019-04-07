<?php declare(strict_types=1);

namespace ReactiveApps\Tests\Command\HttpServer\Middleware;

use function React\Promise\resolve;
use RingCentral\Psr7\Response;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;

/**
 * @internal
 */
final class RequestHandlerStub extends AsyncTestCase
{
    public static function handlerRequest()
    {
        return new Response(123);
    }

    public static function handlerCoroutineRequest()
    {
        yield resolve();
        yield resolve();
        yield resolve();
        yield resolve();

        return new Response(123);
    }
}
