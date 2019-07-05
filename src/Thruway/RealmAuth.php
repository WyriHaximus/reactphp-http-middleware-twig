<?php declare(strict_types=1);

namespace ReactiveApps\Command\HttpServer\Thruway;

final class RealmAuth
{
    /** @var bool */
    private $enable;

    /** @var string */
    private $key;

    public function __construct(bool $enable, string $key)
    {
        $this->enable = $enable;
        $this->key = $key;
    }

    public function isEnabled(): bool
    {
        return $this->enable;
    }

    public function getKey(): string
    {
        return $this->key;
    }
}
