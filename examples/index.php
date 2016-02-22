<?php

require __DIR__ . '/../vendor/autoload.php';

$whoops = new \Whoops\Run;
$whoops->pushHandler(\SoWhoops\HandlerFactory::buildDefaultDecorator());
$whoops->register();

$new->test; // Throws an error exception (E_NOTICE)