<?php declare(strict_types=1);

namespace ReactiveApps\Tests\Command\HttpServer\Annotations;

use ReactiveApps\Command\HttpServer\Annotations\Route;
use WyriHaximus\TestUtilities\TestCase;

/**
 * @internal
 */
final class RouteTest extends TestCase
{
    public function testGetRoute()
    {
        $route = new Route(['/path/to/route', '/not/path/to/route']);

        self::assertSame('/path/to/route', $route->getRoute());
    }
}
