<?php declare(strict_types=1);

namespace ReactiveApps\Command\HttpServer\Annotations;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
final class Template
{
    /**
     * @var string
     */
    private $template;

    /**
     * @param string[] $templates
     */
    public function __construct(array $templates)
    {
        $this->template = \current($templates);
    }

    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return $this->template;
    }
}
