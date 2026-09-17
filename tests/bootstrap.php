<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

$_SERVER['APP_ENV'] ??= 'test';

if (method_exists(Dotenv::class, 'bootEnv')) {
    $dotenv = new Dotenv();
    $dotenv->bootEnv(dirname(__DIR__) . '/.env', $_SERVER['APP_ENV']);
}

if ($_SERVER['APP_DEBUG'] ?? false) {
    umask(0000);
}
