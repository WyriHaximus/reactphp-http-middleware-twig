<?php declare(strict_types=1);

namespace ReactiveApps\Tests\Command\HttpServer;

use React\EventLoop\Factory;
use function React\Promise\Stream\buffer;
use ReactiveApps\Command\HttpServer\JsonResponse;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
use WyriHaximus\React\Stream\Json\JsonStream;

/**
 * @internal
 */
final class JsonResponseTest extends AsyncTestCase
{
    public function testDataTransfer(): void
    {
        $loop = Factory::create();
        $data = [
            'bool' => [
                false,
                true,
            ],
            'string' => 'beer',
            'int' => \time(),
        ];

        $jsonStream = new JsonStream();

        $loop->addTimer(0.1, function () use ($jsonStream, $data): void {
            $jsonStream->end($data);
        });

        $response = JsonResponse::create(666, [], $jsonStream);

        /** @var JsonStream $body */
        $body = $response->getBody();

        $body = $this->await(buffer($body), $loop);
        self::assertSame(\json_encode($data), $body);
    }
}
