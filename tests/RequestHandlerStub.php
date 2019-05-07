<?php declare(strict_types=1);

namespace ReactiveApps\Tests\Command\HttpServer;

final class RequestHandlerStub
{
    private static $staticHandlerCalled = false;
    private $handlerCalled = false;

    public static function methodStatic(): void
    {
        self::$staticHandlerCalled = true;
    }

    public static function getStaticHandlerCalled(): bool
    {
        return self::$staticHandlerCalled;
    }

    public static function resetStaticHandlerCalled(): void
    {
        self::$staticHandlerCalled = false;
    }

    public function method(): void
    {
        $this->handlerCalled = true;
    }

    public function isHandlerCalled(): bool
    {
        return $this->handlerCalled;
    }
}
