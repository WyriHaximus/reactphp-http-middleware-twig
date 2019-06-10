<?php declare(strict_types=1);

namespace ReactiveApps\Command\HttpServer\Event;

use ReactiveApps\Command\HttpServer\Thruway\Realm;
use Thruway\ClientSession;

final class RealmClose
{
    /** @var Realm */
    private $realm;

    /** @var ClientSession */
    private $session;

    public function __construct(Realm $realm, ClientSession $session)
    {
        $this->realm = $realm;
        $this->session = $session;
    }

    public function getRealm(): Realm
    {
        return $this->realm;
    }

    public function getSession(): ClientSession
    {
        return $this->session;
    }
}
