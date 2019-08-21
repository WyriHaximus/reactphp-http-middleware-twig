<?php declare(strict_types=1);

namespace ReactiveApps\Tests\Command\HttpServer\Controller;

use ReactiveApps\Command\HttpServer\Controller\JWT;
use ReactiveApps\Command\HttpServer\Exception\UnknownRealmException;
use ReactiveApps\Command\HttpServer\TemplateResponse;
use ReactiveApps\Command\HttpServer\Thruway\Realm;
use ReactiveApps\Command\HttpServer\Thruway\RealmAuth;
use RingCentral\Psr7\ServerRequest;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;

/**
 * @internal
 */
final class JWTTest extends AsyncTestCase
{
    /** @var JWT */
    private $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new JWT([
            'default' => new Realm('default', new RealmAuth(true, 'key'), []),
            'no-auth' => new Realm('no-auth', new RealmAuth(false, ''), []),
            'auth-empty-key' => new Realm('auth-empty-key', new RealmAuth(true, ''), []),
        ]);
    }

    public function testGenerateToken(): void
    {
        $request = (new ServerRequest(
            'GET',
            'https://example.com/thruway/jwt/token.json?realm=default'
        ))->withQueryParams(['realm' => 'default']);

        $response = $this->controller->token($request);

        self::assertInstanceOf(TemplateResponse::class, $response);
    }

    public function testUnknownRealm(): void
    {
        self::expectException(UnknownRealmException::class);
        self::expectExceptionMessage('Unknown Realm: unknown');

        $request = (new ServerRequest(
            'GET',
            'https://example.com/thruway/jwt/token.json?realm=unknown'
        ))->withQueryParams(['realm' => 'unknown']);

        $this->controller->token($request);
    }

    public function testNoAuthOnRealm(): void
    {
        self::expectException(UnknownRealmException::class);
        self::expectExceptionMessage('Unknown Realm: no-auth');

        $request = (new ServerRequest(
            'GET',
            'https://example.com/thruway/jwt/token.json?realm=no-auth'
        ))->withQueryParams(['realm' => 'no-auth']);

        $this->controller->token($request);
    }

    public function testAuthOnRealmButEmptyKey(): void
    {
        self::expectException(UnknownRealmException::class);
        self::expectExceptionMessage('Unknown Realm: auth-empty-key');

        $request = (new ServerRequest(
            'GET',
            'https://example.com/thruway/jwt/token.json?realm=auth-empty-key'
        ))->withQueryParams(['realm' => 'auth-empty-key']);

        $this->controller->token($request);
    }
}
