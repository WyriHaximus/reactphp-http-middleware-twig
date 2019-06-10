<?php declare(strict_types=1);

namespace ReactiveApps\Command\HttpServer\Event;

use Thruway\ClientSession;

final class RealmClose
{
    /** @var string */
    private $realm;

    /** @var ClientSession */
    private $session;

    public function __construct(string $realm, ClientSession $session)
    {
        $this->realm = $realm;
        $this->session = $session;
    }

    public function getRealm(): string
    {
        return $this->realm;
    }

    public function getSession(): ClientSession
    {
        return $this->session;
    }
}
