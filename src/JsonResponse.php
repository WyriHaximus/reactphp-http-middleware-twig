<?php declare(strict_types=1);

namespace ReactiveApps\Command\HttpServer;

use Psr\Http\Message\ResponseInterface;
use React\Http\Response;
use WyriHaximus\React\Stream\Json\JsonStream;

final class JsonResponse
{
    /**
     * @param int        $status  Status code for the response, if any.
     * @param array      $headers Headers for the response, if any.
     * @param JsonStream $body    Stream body.
     * @param string     $version Protocol version.
     * @param string     $reason  Reason phrase (a default will be used if possible).
     */
    public static function create(
        $status = 200,
        array $headers = [],
        JsonStream $body,
        string $version = '1.1',
        string $reason = ''
    ): ResponseInterface {
        return new Response(
            $status,
            $headers,
            $body,
            $version,
            $reason !== '' ? $reason : null
        );
    }
}
