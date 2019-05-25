<?php declare(strict_types=1);

namespace ReactiveApps\Tests\Command\HttpServer\Integration\Annotations\Stub;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReactiveApps\Command\HttpServer\Annotations\Method;
use ReactiveApps\Command\HttpServer\Annotations\Routes;
use RingCentral\Psr7\Response;

final class Controller
{
    /**
     * @Method("GET")
     * @Routes("/")
     * @param  ServerRequestInterface $request
     * @return ResponseInterface
     */
    public static function single(ServerRequestInterface $request): ResponseInterface
    {
        return new Response();
    }

    /**
     * @Method("GET")
     * @Routes({"/"})
     * @param  ServerRequestInterface $request
     * @return ResponseInterface
     */
    public static function one(ServerRequestInterface $request): ResponseInterface
    {
        return new Response();
    }

    /**
     * @Method("GET")
     * @Routes({
     *     "/bar",
     *     "/beer"
     * })
     * @param  ServerRequestInterface $request
     * @return ResponseInterface
     */
    public static function two(ServerRequestInterface $request): ResponseInterface
    {
        return new Response();
    }
}
