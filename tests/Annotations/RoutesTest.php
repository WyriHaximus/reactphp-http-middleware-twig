<?php declare(strict_types=1);

namespace ReactiveApps\Tests\Command\HttpServer\Annotations;

use ReactiveApps\Command\HttpServer\Annotations\Routes;
use WyriHaximus\TestUtilities\TestCase;

/**
 * @internal
 */
final class RoutesTest extends TestCase
{
    public function testGetRoute(): void
    {
        $routesList = ['/path/to/route', '/not/path/to/route'];
        $routes = new Routes(['value' => $routesList]);

        self::assertSame($routesList, $routes->getRoutes());
    }
}
