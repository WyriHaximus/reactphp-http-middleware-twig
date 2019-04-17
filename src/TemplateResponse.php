<?php declare(strict_types=1);

namespace ReactiveApps\Command\HttpServer;

use RingCentral\Psr7\Response;

final class TemplateResponse extends Response
{
    /** @var array  */
    private $data = [];

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }
}
