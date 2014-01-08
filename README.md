# Dynamic Dependency Injection

[![Build Status](https://travis-ci.org/pierredup/Di.png?branch=master)](https://travis-ci.org/pierredup/Di)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/pierredup/Di/badges/quality-score.png?s=cd1d6320b059902f1a4738707fe75b5ab92bce99)](https://scrutinizer-ci.com/g/pierredup/Di/)
[![Code Coverage](https://scrutinizer-ci.com/g/pierredup/Di/badges/coverage.png?s=73ba6e3e0c0fbeed93dfe857435381b6eb5d8589)](https://scrutinizer-ci.com/g/pierredup/Di/)

This library provides a class to handle dependency injection dynamically.

Dynamic dependency injection means you don't have to define services or define which dependencies
needs to be injected. The dependencies is dynamically created based on the constructor parameters.

This means you only need to add type-hints for the classes you want to inject into your class constructor.

## Installation

### Composer

To install this library using [composer](http://getcomposer.org/), add the following to your `composer.json`:

```json
{
    "require": {
        "pierredup/di": "*"
    }
}
```

Make sure you are using composer's autoloader to include the file:

```php
require 'vendor/autoload.php';
```

### Download the file

Download [Di.phar](https://github.com/pierredup/Di/blob/master/Di.phar) from the repo and save the file into your project path somewhere.

```php
require 'path/to/Di.phar';
```

## Usage

### Getting a class with dependencies

```php
namespace Foo {

    class Bar
    {
        public function __construct(Baz $baz)
        {
            // ...
        }
    }

    class Baz {

    }
}

$object = Di::get('Foo\Bar');

var_dump($object);
```

This will give you an instance of `Foo\Bar` with the `Baz` class dynamically created and injection into the constructor.

### Getting a new instance of a class

By default, the same instance for each class will be returned every time you call `Di::get`
If you want to return a new instance of a class, you need to pass a second parameter to the `get` method

```php
    $object = Di::get('Foo\Bar', Di::NEW_INSTANCE);
```

This will return a new instance of the `Foo\Bar` class, but the dependencies will always return the same instance.

If you want to ensure that every object returns a new instance, you need to pass the `Di::DEEP` flag:

```php
    $object = Di::get('Foo\Bar', Di::DEEP);
```

This will return a new instance of `Foo\Bar`, as well as new instances for each dependency.

### Parameters

#### Setting parameters

If you have values in your constructor that can not be type-hinted by an object ( E.G database settings),
you use can use the `map` method to set the values for those parameters:

```php
class Db
{
    public function __construct($host, $username, $password)
    {
        // ...
    }
}

Di::map(array(
    'host'      => 'localhost',
    'username'  => 'user',
    'host'      => 'password',
));

$object = Di::get('Db');
```

**Note:** If you don't specify a value for a parameter that doesn't have a type-hint, `NULL` will be passed as the value.
If you set a default value for the parameter, then the default value will be used instead.

##### Lazy loading parameters

If you want parameters to be lazy-loaded (only load when needed, E.G get value from a database or a session ),
you can use a closure or valid callback as the parameter:

```php
Di::map(array(
    'parameter' => function() {
        return Di::get('Foo\Database')->getValueFromDb()
    }
));

// OR

Di::map(array(
    'parameter' => array($db, 'getValueFromDb')
));

```

The function or callback will only be executed when the parameter is needed, and the value will then be cached
for any further calls.

#### Getting parameters

To get a parameter value, just pass the `Di::PARAM` flag to the get method:

```php
Di::map(array(
    'host'      => 'localhost',
    'username'  => 'user',
    'host'      => 'password',
));

$host = Di::get('host', Di::PARAM); // return 'localhost'
```

#### Over writing classes

You can also use the `map` method to over write a class name.
This is useful if you want to over write a core class of a library with your own implementation.
Note that your class needs to extend from the class you are over writing, otherwise you might get
an error or unexpected results.

```php
namespace Foo {
    class Bar
    {

    }
}

namespace Baz {
    use Foo\Bar;

    class FooBar extends Bar
    {

    }
}

Di::map(array(
    'Foo\Bar' => 'Baz\FooBar'
));

$object = Di::get('Foo\Bar'); // will return an instance of `Baz\FooBar`
```

## TODO

* Setter Injection
* Cache class instances
* Define services