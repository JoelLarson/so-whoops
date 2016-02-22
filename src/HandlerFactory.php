<?php

namespace SoWhoops;

use SoWhoops\SearchAlgorithm\ExactAnswers;
use SoWhoops\SearchAlgorithm\PrefixAnswers;
use Whoops\Handler\PrettyPageHandler;

/**
 * Class HandlerFactory
 * @package SoWhoops
 */
class HandlerFactory
{
    /**
     * @return StackOverflowPrettyPageHandlerDecorator
     */
    public static function buildDefaultDecorator()
    {
        return new StackOverflowPrettyPageHandlerDecorator(
            new PrettyPageHandler(),
            [
                new ExactAnswers(),
                new PrefixAnswers()
            ]
        );
    }
}