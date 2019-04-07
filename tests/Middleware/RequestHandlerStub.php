<?php declare(strict_types=1);

namespace ReactiveApps\Tests\Command\HttpServer\Middleware;

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
}
