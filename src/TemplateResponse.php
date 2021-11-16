<?php

declare(strict_types=1);

namespace WyriHaximus\React\Http\Middleware;

use RingCentral\Psr7\Response;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class TemplateResponse extends Response
{
    private string $template;

    /** @var array<string, mixed> */
    private array $templateData = [];

    public function template(): string
    {
        return $this->template;
    }

    public function withTemplate(string $template): TemplateResponse
    {
        $clone           = clone $this;
        $clone->template = $template;

        return $clone;
    }

    /**
     * @return array<string, mixed>
     */
    public function templateData(): array
    {
        return $this->templateData;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function withTemplateData(array $data): TemplateResponse
    {
        $clone               = clone $this;
        $clone->templateData = $data;

        return $clone;
    }
}
