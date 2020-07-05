<?php

use function Baka\appPath;
use Dotenv\Dotenv;
use Phalcon\Loader;

// Register the auto loader
//require '/baka/src/functions.php';
require dirname(__DIR__) . DIRECTORY_SEPARATOR . '/vendor/baka/src/functions.php';

$loader = new Loader();
$namespaces = [
    'Canvas' => appPath('/src'),
    'Canvas\Cli\Tasks' => appPath('/cli/tasks'),
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
