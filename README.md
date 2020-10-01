# JSON Query

This library can be used to access JSON data with a document database (like MongoDB, CouchDB) 
like property query syntax. 

It is NOT an exact Dot-Notion like approach to array structures. 
It (imho) needs a JSON array / object structure to work that way. 

## Installation

```bash
composer require ronolo/json-query
```

If that does not work, you may have to add the repository to the top level composer.json like this:

```json
{
  "repositories": [
     {
        "type": "vcs",
        "url":  "https://github.com/ronolo/jsonquery.git"
    }
  ]
}
```

## Usage

```php
use RoNoLo\JsonQuery;

$q = JsonQuery::fromFile('data.json'); // or ...
$q = JsonQuery::fromData(['foo' => 1, 'bar' => 2]); // or ...
$q = JsonQuery::fromJson('{"foo": "bernd", "bar": "kitty"}');

$result = $q->query('foo'); // or ...
$result = $q->query('foo.bar'); // or ...
$result = $q->query('foo.2.bar'); // or ...
$result = $q->query('foo.2.bar.0.name'); // or ...
$result = $q->query('foo.bar.name'); // or ...
```

## Examples

Please check the PhpUnit tests, to find more examples.

```json
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

$result = $q->query('population'); // 80523700
$result = $q->query('bernd'); // ValueNotFound()
$result = $q->query('persons.name'); // ["Karl", "Jenni"]
$result = $q->query('persons.1.name'); // "Jenni"
$result = $q->query('latlng'); // [51, 9]
$result = $q->query('latlng.0'); // 51
```
## Limitations

Because of the nature of multidimensional arrays queries which are very deep into the structure, the 
result will be with multidimensional arrays. See PhpUnit example with 'persons.hobby.type' as the query. 

