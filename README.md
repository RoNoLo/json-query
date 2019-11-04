# JSON Query

This library can be used to access JSON data with a document database (like MongoDB, CouchDB) 
like property access syntax.

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

## Examples

Please check the PhpUnit tests, to find more examples.

```javascript
// JSON to load
{
  "de": "Deutschland", "en": "Germany", "es": "Alemania", "fr": "Allemagne",
  "it": "Germania", "ja": "\u30c9\u30a4\u30c4", "nl": "Duitsland",
  "altSpellings": ["DE", "Federal Republic of Germany", "Bundesrepublik Deutschland"],
  "population": 80523700,
  "latlng": [51, 9],
  "persons": [
    {
      "name": "Karl",
      "hobby": [
        {
          "type": "Cars",
          "examples": [
            {"name": "Honda"},
            {"name": "Porsche", "build": ["1962", "1976", "2010"]},
            {"name": "Mercedes"}
          ]
        },
        {
          "type": "Planes",
          "examples": [{"name": "Concorde"}, {"name": "Airbus"}, {"name": "Tupolev"}]
        },
        {
          "type": "Music",
          "examples": [{"name": "Rock"}, {"name": "Pop"}, {"name": "Funk"}]
        }
      ]
    },
    {
      "name": "Jenni",
      "hobby": [
        {
          "type": "Comics",
          "examples": [{"name": "Duffy Duck"}, {"name": "Batman"}, {"name": "Superman"}]
        },
        {
          "type": "Dancing",
          "examples": [{"name": "Walzer"}, {"name": "Shuffle"}]
        }
      ]
    }
  ]
}
```
 
```php
use RoNoLo\JsonQuery;

$q = JsonQuery::fromFile('data.json');

$result = $q->getNestedProperty('population'); // 80523700
$result = $q->getNestedProperty('bernd'); // ValueNotFound()
$result = $q->getNestedProperty('persons.name'); // ["Karl", "Jenni"]
$result = $q->getNestedProperty('persons.1.name'); // "Jenni"
$result = $q->getNestedProperty('latlng'); // [51, 9]
$result = $q->getNestedProperty('latlng.0'); // 51
```
## Limitations

Because of the nature of multidimensional arrays querys which are very deep into the structure, the 
result will be with multidimensional arrays. See example with 'persons.hobby.type' as the query. 

