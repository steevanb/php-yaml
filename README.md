[![version](https://img.shields.io/badge/version-1.0.2-green.svg)](https://github.com/steevanb/php-yaml/tree/1.0.2)
[![symfony](https://img.shields.io/badge/symfony/yaml-^3.1-blue.svg)](https://github.com/symfony/yaml)
![Lines](https://img.shields.io/badge/code%20lines-214-green.svg)
![Total Downloads](https://poser.pugx.org/steevanb/php-yaml/downloads)
[![SensionLabsInsight](https://img.shields.io/badge/SensionLabsInsight-platinum-brightgreen.svg)](https://insight.sensiolabs.com/projects/00a42deb-03af-40da-a61f-b8abdd3a90b3/analyses/9)
[![Scrutinizer](https://scrutinizer-ci.com/g/steevanb/php-yaml/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/steevanb/php-yaml/)

php-yaml
========

Add features to [symfony/yaml](https://github.com/symfony/yaml).

As _steevanb\PhpYaml\Parser_ extends _Symfony\Component\Yaml\Parser_,
you have all Symfony YAML parser features.

[Changelog](changelog.md)

Installation
------------

Use _composer require_ :
```bash
composer require steevanb/php-yaml ^1.0
```

Or add it manually to _composer.json_ :
```yaml
{
    "require": {
        "steevanb/php-yaml": "^1.0"
    }
}
```

How to use
----------

Instead of calling _Symfony\Component\Yaml\Yaml::parse($content)_, you have to do this :
```php
use steevanb\PhpYaml\Parser;
$parser = new Parser();
$parser->parse(file_get_contents('foo.yml'));
```

Function support
----------------

You can call registered functions in yaml value :
````yaml
foo:
    call_file_get_contents: <file('baz.txt')>
    call_new_DateTime: <date()>
    call_new_DateTime2: <date('2017-12-31')>
````
By default, no function is registered into Parser. You need to manually register every function you need.

You can register _<file($fileName)>_ function. _$path_ is _$fileName_ path prefix :
```php
steevanb\PhpYaml\Parser::registerFileFunction($path = null);
```

You can register _<date($format = null)>_ function :
```php
steevanb\PhpYaml\Parser::registerDateFunction();
```

You can register your own function :
```php
steevanb\PhpYaml\Parser::registerFunction('foo', function($bar, $baz) {
    return $bar + $baz;
});
```
And call it in yaml :
```yaml
foo:
    bar: <foo(41, 1)>
```
