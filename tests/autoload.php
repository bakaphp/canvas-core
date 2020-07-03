<?php

use function Baka\appPath;
use Dotenv\Dotenv;
use Phalcon\Loader;

// Register the auto loader
require __DIR__ . '/../src/Core/functions.php';
require '/baka/src/functions.php';
// require dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . '/vendor/canvas/core/src/Core/functions.php';

$loader = new Loader();
$namespaces = [
    'Baka' => '/baka/src/',
    'Canvas' => appPath('/src'),
    'Canvas\Cli\Tasks' => appPath('/cli/tasks'),
    'Niden\Tests' => appPath('/tests'),
    'Canvas\Tests' => appPath('/tests'),
];

$loader->registerNamespaces($namespaces);

$loader->register();

/**
 * Composer Autoloader.
 */
require appPath('vendor/autoload.php');

// Load environment
$dotenv = Dotenv::createImmutable(appPath());
$dotenv->load();
