<?php

/**
 * thanks to https://github.com/limingxinleo.
 */

namespace Canvas\Http;

use Baka\Http\Request\Swoole;

/**
 * Class SwooleRequest.
 *
 * To use Swoole Server with Phalcon we need to overwrite the Phalcon Request Object to use swoole Response object
 * Since swoole is our server he is the one who get all our _GET , _FILES, _POST , _PUT request and we need to parse that info
 * to make our phalcon project work
 *
 * @package Canvas\Http
 *
 * @property \Phalcon\Di $di
 */
class SwooleRequest extends Swoole
{
}
