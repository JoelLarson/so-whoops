# SoWhoops
## StackOverflow Whoops integration

This is a prototype project for integrating stackoverflow with the `PrettyPageHandler` of Whoops!.

To use, wrap the instance of `PrettyPageHandler` with the `StackOverflowPrettyPageHandlerDecorator`:

```php
$whoops = new \Whoops\Run;
$whoops->pushHandler(
    new \SoWhoops\StackOverflowPrettyPageHandlerDecorator(
        new \Whoops\Handler\PrettyPageHandler
    )
);
$whoops->register();
```

To test the example:

```bash
$ composer run-script run-example
```

Connect at: http://127.0.0.1:8000