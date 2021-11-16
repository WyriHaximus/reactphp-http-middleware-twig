<?php

declare(strict_types=1);

namespace WyriHaximus\React\Tests\Http\Middleware;

use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
use WyriHaximus\React\Http\Middleware\TemplateResponse;

use function time;

/**
 * @internal
 */
final class TemplateResponseTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function dataTransfer(): void
    {
        $data     = [
            'bool' => [
                false,
                true,
            ],
            'string' => 'beer',
            'int' => time(),
        ];
        $template = 'frontpage';

        $rawResponse = new TemplateResponse();
        $response    = $rawResponse->withTemplateData($data);
        self::assertSame($data, $response->templateData());
        self::assertNotSame($rawResponse, $response);
        $response = $rawResponse->withTemplate($template);
        self::assertSame($template, $response->template());
        self::assertNotSame($rawResponse, $response);
    }
}
