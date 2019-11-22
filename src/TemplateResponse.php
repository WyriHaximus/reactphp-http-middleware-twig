<?php declare(strict_types=1);

namespace WyriHaximus\React\Http\Middleware;

use RingCentral\Psr7\Response;

final class TemplateResponse extends Response
{
    /** @var string */
    private $template;

    /** @var mixed[] */
    private $templateData = [];

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function withTemplate(string $template): TemplateResponse
    {
        $clone = clone $this;
        $clone->template = $template;

        return $clone;
    }

    /**
     * @return mixed[]
     */
    public function getTemplateData(): array
    {
        return $this->templateData;
    }

    /**
     * @param mixed[] $data
     */
    public function withTemplateData(array $data): TemplateResponse
    {
        $clone = clone $this;
        $clone->templateData = $data;

        return $clone;
    }
}
