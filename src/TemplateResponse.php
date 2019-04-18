<?php declare(strict_types=1);

namespace ReactiveApps\Command\HttpServer;

use RingCentral\Psr7\Response;

final class TemplateResponse extends Response
{
    /** @var array  */
    private $templateData = [];

    public function getTemplateData(): array
    {
        return $this->templateData;
    }

    public function withTemplateData(array $data): TemplateResponse
    {
        $clone = clone $this;
        $clone->templateData = $data;

        return $clone;
    }
}
