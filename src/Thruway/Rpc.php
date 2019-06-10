<?php declare(strict_types=1);

namespace ReactiveApps\Command\HttpServer\Thruway;

final class Rpc
{
    /** @var string */
    private $name;

    /** @var string */
    private $command;

    public function __construct(string $name, string $command)
    {
        $this->name = $name;
        $this->command = $command;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCommand(): string
    {
        return $this->command;
    }
}
