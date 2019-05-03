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
