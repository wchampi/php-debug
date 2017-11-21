# ChDebug for PHP 

[![Latest Stable Version](https://img.shields.io/packagist/v/ch/debug.svg)](https://packagist.org/packages/ch/debug)


ChDebug send your variables to files, Slack's Incoming Webhooks. You can build your custom dumpers for advanced debug strategies.

## Installation

Install the latest version with

```bash
$ composer require ch/debug
```

## Basic Usage

```php
<?php

use Ch\Debug\Debug;

$object = new stdClass();
$object->a = 1;
$object->b = 'test';
$object->c = true;
Debug::_($object);

// You can use filters
$tag = 'string to filter';
$pos = 1;
Debug::_($pos, $tag);
```

```bash
$ DEBUG=* php anyfile.php
[PATH]/anyfile.php(9): $object = object(stdClass)#21 (3) {
  ["a"] => int(1)
  ["b"] => string(4) "test"
  ["c"] => bool(true)
}
[PATH]/anyfile.php(14): $pos = 1 [string to filter]
```
```bash
$ DEBUG=filter php anyfile.php
[PATH]/anyfile.php(14): $pos = 1 [string to filter]
```

## About

### Requirements

- ChDebug works with PHP 5.4+.

### Author

Wilson Champi - <wchampi86@gmail.com> - <http://twitter.com/wchampi>

### License

ChDebug is licensed under the MIT License - see the `LICENSE` file for details

### Acknowledgements

This library is inspired by Node's [debug](https://www.npmjs.com/package/debug) and PHP's [monolog](https://github.com/Seldaek/monolog) library.
