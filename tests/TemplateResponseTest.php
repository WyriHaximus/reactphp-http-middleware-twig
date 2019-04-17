<?php declare(strict_types=1);

namespace ReactiveApps\Tests\Command\HttpServer;

use ReactiveApps\Command\HttpServer\TemplateResponse;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;

/**
 * @internal
 */
final class TemplateResponseTest extends AsyncTestCase
{
    public function testDataTransfer(): void
    {
        $data = [
            'bool' => [
                false,
                true,
            ],
            'string' => 'beer',
            'int' => \time(),
        ];
        $response = (new TemplateResponse())->withData($data);
        self::assertSame($data, $response->getData());
    }
}
