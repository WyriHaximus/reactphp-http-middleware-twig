<?php declare(strict_types=1);

namespace ReactiveApps\Command\HttpServer\Exception;

use Exception;

final class UnknownRealmException extends Exception
{
    /** @var string */
    private $realm;

    public static function create(string $realm): self
    {
        $self = new self('Unknown Realm: ' . $realm);
        $self->realm = $realm;

        return $self;
    }

    public function getRealm(): string
    {
        return $this->realm;
    }
}
