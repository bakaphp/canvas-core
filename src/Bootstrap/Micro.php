<?php

namespace Canvas\Bootstrap;

use Phalcon\Mvc\Micro as PhalconMicro;
use Illuminate\Http\Request;
use Phalcon\Di;

/**
 * Phalcon\Mvc\Micro
 *
 * With Phalcon you can create "Micro-Framework like" applications. By doing
 * this, you only need to write a minimal amount of code to create a PHP
 * application. Micro applications are suitable to small applications, APIs and
 * prototypes in a practical way.
 *
 *```php
 * $app = new \Phalcon\Mvc\Micro();
 *
 * $app->get(
 *     "/say/welcome/{name}",
 *     function ($name) {
 *         echo "<h1>Welcome $name!</h1>";
 *     }
 * );
 *
 * $app->handle("/say/welcome/Phalcon");
 *```
 */
class Micro extends PhalconMicro
{
    /**
     * Handle the whole request
     *
     * @param string uri
     * @return mixed
     */
    public function dispatch(Request $request)
    {
        //Get router
        $router = Di::getDefault()->get('router');

        $response = $router->dispatch($request);
        $response->send();
    }
}
