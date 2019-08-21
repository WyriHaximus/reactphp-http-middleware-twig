<?php declare(strict_types=1);

namespace ReactiveApps\Command\HttpServer\Thruway;

final class Realm
{
    /** @var string */
    private $name;

    /** @var RealmAuth */
    private $auth;

    /** @var Rpc[] */
    private $rpcs = [];

    /**
     * @param string    $name
     * @param RealmAuth $auth
     * @param Rpc[]     $rpcs
     */
    public function __construct(string $name, RealmAuth $auth, array $rpcs)
    {
        $this->name = $name;
        $this->auth = $auth;
        $this->rpcs = $rpcs;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAuth(): RealmAuth
    {
        return $this->auth;
    }

    /**
     * @return Rpc[]
     */
    public function getRpcs(): iterable
    {
        yield from $this->rpcs;
    }
}
