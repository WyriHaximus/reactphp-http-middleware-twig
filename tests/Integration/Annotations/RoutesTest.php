<?php declare(strict_types=1);

namespace ReactiveApps\Tests\Command\HttpServer\Integration\Annotations;

use Doctrine\Common\Annotations\AnnotationReader;
use ReactiveApps\Command\HttpServer\Annotations\Routes;
use ReactiveApps\Tests\Command\HttpServer\Integration\Annotations\Stub\Controller;
use WyriHaximus\TestUtilities\TestCase;

/**
 * @internal
 */
final class RoutesTest extends TestCase
{
    /**
     * @test
     */
    public function single(): void
    {
        $reader = new AnnotationReader();
        /** @var Routes $annotation */
        $annotation = $reader->getMethodAnnotation(
            new \ReflectionMethod(
                Controller::class,
                'single'
            ),
            Routes::class
        );

        self::assertSame([
            '/',
        ], $annotation->getRoutes());
    }

    /**
     * @test
     */
    public function one(): void
    {
        $reader = new AnnotationReader();
        /** @var Routes $annotation */
        $annotation = $reader->getMethodAnnotation(
            new \ReflectionMethod(
                Controller::class,
                'one'
            ),
            Routes::class
        );

        self::assertSame([
            '/',
        ], $annotation->getRoutes());
    }

    /**
     * @test
     */
    public function two(): void
    {
        $reader = new AnnotationReader();
        /** @var Routes $annotation */
        $annotation = $reader->getMethodAnnotation(
            new \ReflectionMethod(
                Controller::class,
                'two'
            ),
            Routes::class
        );

        self::assertSame([
            '/bar',
            '/beer',
        ], $annotation->getRoutes());
    }
}
