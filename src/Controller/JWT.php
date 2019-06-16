<?php declare(strict_types=1);

namespace ReactiveApps\Command\HttpServer\Controller;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReactiveApps\Command\HttpServer\Annotations\Method;
use ReactiveApps\Command\HttpServer\Annotations\Routes;
use ReactiveApps\Command\HttpServer\Annotations\Template;
use ReactiveApps\Command\HttpServer\Exception\UnknownRealmException;
use ReactiveApps\Command\HttpServer\TemplateResponse;
use function WyriHaximus\getIn;
use WyriHaximus\React\Http\Middleware\Session;
use WyriHaximus\React\Http\Middleware\SessionMiddleware;

final class JWT
{
    /** @var Realm[] */
    private $realms = [];

    public function __construct(array $realms)
    {
        $this->realms = $realms;
    }

    /**
     * @Method("GET")
     * @Routes({
     *     "/thruway/jwt/token.json",
     *     "/thruway/jwt/{realm:[a-zA-Z0-9\-\_]{1,}}.json"
     * })
     * @Template("thruway/jwt/token")
     *
     * @param  ServerRequestInterface $request
     * @throws UnknownRealmException
     * @return ResponseInterface
     */
    public function token(ServerRequestInterface $request): ResponseInterface
    {
        $realm = $request->getQueryParams()['realm'];
        if (!isset($this->realms[$realm])) {
            throw UnknownRealmException::create($realm);
        }
        if ($this->realms[$realm]->getAuth()->isEnabled() === false) {
            throw UnknownRealmException::create($realm);
        }

        if ($this->realms[$realm]->getAuth()->isEnabled() === true && $this->realms[$realm]->getAuth()->getKey() === '') {
            throw UnknownRealmException::create($realm);
        }

        /** @var Session $session */
        $session = $request->getAttribute(SessionMiddleware::ATTRIBUTE_NAME);

        $realmSalt = \getenv('THRUWAY_REALM_SALT');
        $authKeySalt = \getenv('THRUWAY_AUTH_KEY_SALT');
        $hashedRealm = \hash('sha512', $realmSalt . $realm . $realmSalt);
        $hashedRealm = \base64_encode($hashedRealm);
        $token = (new Builder())
            ->setIssuer($hashedRealm)
            ->setAudience($hashedRealm)
            ->setId(\bin2hex(\random_bytes(\mt_rand(256, 512))), true)
            ->setIssuedAt(\time())
            ->setNotBefore(\time() - 13)
            ->setExpiration(\time() + 13)
            ->set('authid', $session === null ? 0 : getIn($session->getContents(), 'user.id', 0))
            ->sign(new Sha256(), $authKeySalt . $this->realms[$realm]->getAuth()->getKey() . $authKeySalt)
            ->getToken();

        return (new TemplateResponse())->withTemplateData([
            'token' => $token,
        ]);
    }
}
