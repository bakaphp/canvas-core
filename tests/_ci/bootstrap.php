<?php

use Canvas\Bootstrap\Tests;

require_once __DIR__ . '/../../src/Core/autoload.php';

$bootstrap = new Tests();
$bootstrap->setup();

return $bootstrap->run();
