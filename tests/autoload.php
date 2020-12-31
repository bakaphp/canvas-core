<?php

require __DIR__ . '/../vendor/autoload.php';

use function Baka\appPath;
use Dotenv\Dotenv;
use Phalcon\Loader;

// Register the auto loader
//require '/baka/src/functions.php';
//require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor/baka/baka/src/functions.php';

// Load environment
$dotenv = Dotenv::createImmutable(appPath());
$dotenv->load();

$loader = new Loader();
$namespaces = [
    //'Baka' => '/baka/src',
    'Canvas' => appPath('/src'),
    'Canvas\Cli\Tasks' => appPath('/cli/tasks'),
    'Canvas\Tests' => appPath('/tests'),
];

$loader->registerNamespaces($namespaces);
$loader->register();
