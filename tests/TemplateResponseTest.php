<?php declare(strict_types=1);

namespace WyriHaximus\React\Tests\Http\Middleware;

use WyriHaximus\React\Http\Middleware\TemplateResponse;
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
        $template = 'frontpage';

        $response = (new TemplateResponse())->withTemplateData($data)->withTemplate($template);
        self::assertSame($data, $response->getTemplateData());
        self::assertSame($template, $response->getTemplate());
    }
}
