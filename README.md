# SoWhoops
## StackOverflow Whoops integration

This is a prototype project for integrating stackoverflow with the `PrettyPageHandler` of Whoops!.

To use:

```php
$whoops = new \Whoops\Run;
$whoops->pushHandler(\SoWhoops\HandlerFactory::buildDefaultDecorator());
$whoops->register();
```

Advanced configuration:

```php
$whoops = new \Whoops\Run;
$whoops->pushHandler(
    new \SoWhoops\StackOverflowPrettyPageHandlerDecorator(
        new \Whoops\Handler\PrettyPageHandler,
        [
            new \SoWhoops\SearchAlgorithm\ExactAnswers,
            new \SoWhoops\SearchAlgorithm\PrefixAnswers
        ]
    )
);
$whoops->register();
```

To test the example:

```bash
$ composer run-example
```

Connect at: http://127.0.0.1:8000