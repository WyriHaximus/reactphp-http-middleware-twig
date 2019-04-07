<?php declare(strict_types=1);

namespace ReactiveApps\Tests\Command\HttpServer\Annotations;

use ReactiveApps\Command\HttpServer\Annotations\Method;
use WyriHaximus\TestUtilities\TestCase;

/**
 * @internal
 */
final class MethodTest extends TestCase
{
    public function testGetMethod()
    {
        $method = new Method(['GET', 'POST']);

        self::assertSame('GET', $method->getMethod());
    }
}
