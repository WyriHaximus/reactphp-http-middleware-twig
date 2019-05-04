# HTTP Server command

[![Build Status](https://travis-ci.com/reactive-apps/command-http-server.svg?branch=master)](https://travis-ci.com/reactive-apps/command-http-server)
[![Latest Stable Version](https://poser.pugx.org/reactive-apps/command-http-server/v/stable.png)](https://packagist.org/packages/reactive-apps/command-http-server)
[![Total Downloads](https://poser.pugx.org/reactive-apps/command-http-server/downloads.png)](https://packagist.org/packages/reactive-apps/command-http-server/stats)
[![Code Coverage](https://scrutinizer-ci.com/g/reactive-apps/command-http-server/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/reactive-apps/command-http-server/?branch=master)
[![License](https://poser.pugx.org/reactive-apps/command-http-server/license.png)](https://packagist.org/packages/reactive-apps/command-http-server)
[![PHP 7 ready](http://php7ready.timesplinter.ch/reactive-apps/command-http-server/badge.svg)](https://travis-ci.com/reactive-apps/command-http-server)

# Install

To install via [Composer](http://getcomposer.org/), use the command below, it will automatically detect the latest version and bind it with `^`.
 
```
composer require reactive-apps/command-http-server 
```

# Controllers

Controllers come in two different flavours static and instantiated controllers. 

## Static Controllers

Static controllers are recommended when your controller doesn't have any dependencies like this ping controller used for 
[`updown.io`](https://updown.io/r/rPWzd) health checks. ***Note: `/ping` isn't a updown standard but it's my personal 
standard of doing health checks for my apps*** This controller only has a single method with a single route and no
dependencies:

```php
<?php declare(strict_types=1);

namespace App\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReactiveApps\Command\HttpServer\Annotations\Method;
use ReactiveApps\Command\HttpServer\Annotations\Routes;
use RingCentral\Psr7\Response;

final class Ping
{
    /**
     * @Method("GET")
     * @Routes("/ping")
     *
     * @param  ServerRequestInterface $request
     * @return ResponseInterface
     */
    public static function ping(ServerRequestInterface $request): ResponseInterface
    {
        return new Response(
            200,
            ['Content-Type' => 'text/plain'],
            'pong'
        );
    }
}
```

## Instantiated Controllers

Instantiated Controllers on the other hand will be instantiated and kept around to handle more requests in the future 
as such they can have dependencies injected. The example below is a controller that has the event loop injected to wait 
for a random number of seconds before returning the response. It also uses coroutines to make the code more readable: 

```php
<?php declare(strict_types=1);

namespace App\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\LoopInterface;
use ReactiveApps\Command\HttpServer\Annotations\Method;
use ReactiveApps\Command\HttpServer\Annotations\Routes;
use ReactiveApps\Command\HttpServer\Annotations\Template;
use ReactiveApps\Command\HttpServer\TemplateResponse;
use WyriHaximus\Annotations\Coroutine;
use function WyriHaximus\React\timedPromise;

/**
 * @Coroutine())
 */
final class Root
{
    /** @var LoopInterface */
    private $loop;

    /** @var int */
    private $time;

    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
        $this->time = \time();
    }

    /**
     * @Method("GET")
     * @Routes("/")
     * @Template("root")
     *
     * @param  ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function root(ServerRequestInterface $request)
    {
        $start = \time();

        yield timedPromise($this->loop, \random_int(1, 5));

        return (new TemplateResponse(
            200,
            ['Content-Type' => 'text/plain']
        ))->withTemplateData([
            'uptime' => (\time() - $this->time),
            'took' => (\time() - $start),
        ]);
    }
}

```

# Routing

Routing is done through annotations on the method handling the routes. Each method can handle multiple routes but it's 
recommended to only map routes that fit the the method.

For example the following annotation will map the current method to `/` (***note: all routes are required to be 
prefixed with `/`***): `@Routes("/")`

A multi route annotation has a slightly different syntax, in the following both `/old` and `/new` will be handled by 
the same method: 

```php
@Routes({
    "/old",
    "/new"
})
``` 

The underlying engine for routes is [`nikic/fast-route`](https://github.com/nikic/FastRoute) which also makes complex 
routes like this one possible:

```php
@Route("/{map:(?:wow_cata_draenor|wow_cata_land|wow_cata_underwater|wow_legion_azeroth|wow_battle_for_azeroth|wow_cata_elemental_plane|wow_cata_twisting_nether|wow_comp_wotlk)}/{zoom:1|2|3|4|5|6|7|8|9|10}/{width:[0-9]{1,5}}/{height:[0-9]{1,5}}/{center:[a-zA-Z0-9\`\-\~\_\@\%]{1,35}}{blips:/blip\_center|/[a-zA-Z0-9\`\-\~\_\@\%\[\]]{3,}.+|}.{quality:png|hq.jpg|lq.jpg}")
```

The different route components like `map`, and `center` are available from the request object with:

```php
$request->getAttribute('center');
```

# Annotations

* `@ChildProcess` - Runs controller actions inside a child process
* `@Coroutine` - Runs controller actions inside a coroutine
* `@Method` - Allowed HTTP methods (GET, POST, PATCH, etc)
* `@Routes` - Routes to use the given method for
* `@Template` - Template to use when a TemplateResponse is used
* `@Thread` - Runs controller actions inside a thread (preferred over use child processes)

# Options

* `http-server.address` - The IP + Port to listen on, for example: `0.0.0.0:8080`
* `http-server.hsts` - Whether or not to set HSTS headers
* `http-server.public` - Public webroot to serve, note only put files in here everyone is allowed to see
* `http-server.public.preload.cache` - Custom cache to store the preloaded webroot files, stored in memory by default
* `http-server.middleware.prefix` - An array with react/http middleware added before the accesslog and webroot serving middleware
* `http-server.middleware.suffix` - An array with react/http middleware added after the accesslog and webroot serving middleware and before the route middleware and request handler
* `http-server.pool.ttl` - TTL for a child process to wait for it's next task
* `http-server.pool.min` - Minimum number of child processes
* `http-server.pool.max` - maximum number of child processes
* `http-server.rewrites` - Rewrites request path internally from one path to the other, invisible for visitors

# License

The MIT License (MIT)

Copyright (c) 2019 Cees-Jan Kiewiet

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
