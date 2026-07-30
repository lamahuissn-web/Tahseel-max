<?php

require dirname(__DIR__).'/vendor/autoload.php';

$worktreeLoader = new Composer\Autoload\ClassLoader;
$worktreeLoader->addPsr4('App\\', dirname(__DIR__).'/app');
$worktreeLoader->addPsr4('Tests\\', __DIR__);
$worktreeLoader->register(true);
