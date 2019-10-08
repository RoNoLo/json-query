# JSON Query

This library can be used to access JSON data with a document database (like MongoDB) like property access syntax.

## Installation

```
composer require ronolo/jsonquery
```

## Usage

```php
use RoNoLo\JsonQuery;

$q = JsonQuery::fromFile('data.json'); // or ...
$q = JsonQuery::fromData(['foo' => 1, 'bar' => 2]); // or ...
$q = JsonQuery::fromJson('{"foo": "bernd", "bar": "kitty"}');

$result = $q->getNestedProperty('foo'); // or ...
$result = $q->getNestedProperty('foo.bar'); // or ...
$result = $q->getNestedProperty('foo.2.bar'); // or ...
$result = $q->getNestedProperty('foo.2.bar.0.name'); // or ...
$result = $q->getNestedProperty('foo.bar.name'); // or ...
```

Please check the PhpUnit tests, to find more examples. 

## Limitations

to be determained