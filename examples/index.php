<?php

require __DIR__ . '/../vendor/autoload.php';

$whoops = new \Whoops\Run;
$whoops->pushHandler(
    new \SoWhoops\StackOverflowPrettyPageHandlerDecorator(
        new \Whoops\Handler\PrettyPageHandler
    )
);
$whoops->register();

$new->test; // Throws an error exception (E_NOTICE)