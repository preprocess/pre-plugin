# Pre Plugin

This is the basis for all Pre processing. It provides the tools to add custom macro files, compile and autoload `.pre` class files, and format the resulting files for readability.

You can develop your own Pre macros, by following the steps below.

## Using

The first step is to require this repository in your plugin repository:

```
composer require pre/plugin
```

This includes the Pre autoloader, and macro file functions. You can see which macro files are being included, with:

```php
Pre\getMacroPaths(); // ["path/to/macro.pre", ...]
```

Macros are written using [Yay](https://github.com/marcioAlmada/yay) syntax:

```php
<?php

macro {
    replaced(···expression)
} >> {
    with(···expression)
}
```

If you'd like to learn how this syntax works, take a look at [the Pre plugins](https://github.com/prehp), or [the Yay tests](https://github.com/marcioAlmada/yay/tree/master/tests). It's tricky, but well worth it to get your favourite syntax without writing a line of core code.

Add this macro file, using another helper function:

```php
Pre\addMacroPath("path/to/macros.pre");
```

If you decide you'd like to remove the macro (at runtime), there's a third helpful function you can use:

```php
Pre\removeMacroPath("path/to/macros.pre");
```

Pre is opt-in, which means you need to rename your `src/App/User.php` files to `src/App/User.pre`. This extension makes them visible to the Pre autoloader, which will include all registered macro files, compile them to valid PHP syntax, and format these compiled files to [PSR-2](http://www.php-fig.org/psr/psr-2).

## Testing

There are a few tests, to check that the macro helper function work, and that `.pre` files are correctly compiled. You can run these tests with:

```
vendor/bin/phpunit
```

This assumes you've cloned this repository and run `composer install` beforehand.

## Versioning

This library follows [Semver](http://semver.org). According to Semver, you will be able to upgrade to any minor or patch version of this library without any breaking changes to the public API. Semver also requires that we clearly define the public API for this library.

All methods, with `public` visibility, are part of the public API. All other methods are not part of the public API. Where possible, we'll try to keep `protected` methods backwards-compatible in minor/patch versions, but if you're overriding methods then please test your work before upgrading.
