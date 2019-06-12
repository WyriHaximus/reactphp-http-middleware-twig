<?php declare(strict_types=1);

namespace ReactiveApps\Command\HttpServer\Thruway;

final class Realm
{
    /** @var string */
    private $name;

    /** @var Rpc[] */
    private $rpcs = [];

    /**
     * @param string $name
     * @param Rpc[]  $rpcs
     */
    public function __construct(string $name, array $rpcs)
    {
        $this->name = $name;
        $this->rpcs = $rpcs;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Rpc[]
     */
    public function getRpcs(): iterable
    {
        yield from $this->rpcs;
    }
}
