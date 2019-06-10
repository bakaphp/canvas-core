<?php

use Canvas\Bootstrap\Tests;

require_once __DIR__ . '/../../src/Core/functions.php';

$bootstrap = new Tests();
$bootstrap->setup();

return $bootstrap->run();
