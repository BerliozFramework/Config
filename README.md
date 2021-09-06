# Berlioz Configuration

[![Latest Version](https://img.shields.io/packagist/v/berlioz/config.svg?style=flat-square)](https://github.com/BerliozFramework/Config/releases)
[![Software license](https://img.shields.io/github/license/BerliozFramework/Config.svg?style=flat-square)](https://github.com/BerliozFramework/Config/blob/2.x/LICENSE)
[![Build Status](https://img.shields.io/github/workflow/status/BerliozFramework/Config/Tests/2.x.svg?style=flat-square)](https://github.com/BerliozFramework/Config/actions/workflows/tests.yml?query=branch%3A2.x)
[![Quality Grade](https://img.shields.io/codacy/grade/f290647a1f5143ec8299ecea9b83d6b1/2.x.svg?style=flat-square)](https://www.codacy.com/manual/BerliozFramework/Config)
[![Total Downloads](https://img.shields.io/packagist/dt/berlioz/config.svg?style=flat-square)](https://packagist.org/packages/berlioz/config)

**Berlioz Configuration** is a PHP library to manage your configuration files.

## Installation

### Composer

You can install **Berlioz Configuration** with [Composer](https://getcomposer.org/), it's the recommended installation.

```bash
$ composer require berlioz/config
```

### Dependencies

* **PHP** ^8.0
* Packages:
    * **berlioz/helpers**
    * **colinodell/json5**

## Usage

### Create configuration object

You can create the configuration with adapters. 3 default adapters are available:

- `ArrayAdapter`: accept a PHP array
- `IniAdapter`: accept a INI string or file
- `JsonAdapter`: accept a JSON/JSON5 string or file

Example:

```php
use Berlioz\Config\Adapter;
use Berlioz\Config\Config;

$arrayAdapter = new Adapter\ArrayAdapter([/*...*/]);
$iniAdapter = new Adapter\IniAdapter('/path/of-project/config/config.ini', true);
$jsonAdapter = new Adapter\JsonAdapter('/path/of-project/config/config.json', true);

$config = new Config([$arrayAdapter, $jsonAdapter, $iniAdapter]);
print $config->get('foo.bar.qux'); // Print value of configuration
```

Second parameter of `IniAdapter` and `JsonAdapter` constructors define that the first parameter is an url.

The order of adapter is important, the first have priority... So the value returned by `get` method is the first adapter
to respond at key. If the value is an array, it will be merged with all adapters.

For more flexibility, you can define the priority by an integer in the constructor of adapters, withe the
parameter `priority`.

### Get value

To get value, you must call `get` method:

```php
$config = new \Berlioz\Config\Config(/* ... */);

$config->get('foo'); // Returns value of key 'foo'
$config->get('foo.bar'); // Returns value of nested key 'foo.bar'
$config->get('baz', true); // Returns value of key 'baz' or TRUE default value if key does not exist
```

The second parameter of `ConfigInterface::get()` method is the default value if key does not exist. Default value of
this parameter is `NULL`.

You can also test if a key exist:

```php
$config = new \Berlioz\Config\Config(/* ... */);

$exists = $config->has('foo'); // Returns boolean
```

### Functions

`Config` object accept a set of functions. The syntax to call a function is: `{functionName:value}`.

A function call must be alone in value of configuration key.

Defaults functions:

- `config`: replace value by another part of config
- `constant`: replace value by a constant
- `env`: replace value by environment variable
- `var`: replace value by variable value

Examples:

```php
use Berlioz\Config\Adapter;
use Berlioz\Config\Config;

define('FOO', 'foo constant value');

$arrayAdapter = new Adapter\ArrayAdapter([
    'foo' => '{constant:FOO}',
    'bar' => [
        'foo' => 'value2',
    ],
    'baz' => '{config: bar.foo}',
    'qux' => '{var: BAR}'
]);
$config = new Config([$arrayAdapter], ['BAR' => 'bar value']);

print $config->get('foo'); // Print "foo constant value"
print $config->get('baz'); // Print "value2"
print $config->get('qux'); // Print "bar value"
print_r($config->get('bar')); // Print array "['foo' => 'value2']"
```

### Variables

You can define variables usable in configuration with function `var`.

Define variables in the constructor:

```php
// Define variable in an array
$variables = [
    'foo' => 'foo value',
    'bar' => 'bar value',
];

$config = new \Berlioz\Config\Config(variables: $variables);
```

You can also manipulate the variables after instantiation of config. Variables are stored in a `ArrayObject` object,
accessible with `Config::getVariables()` method:

```php
$config = new \Berlioz\Config\Config();

// Set variables
$config->getVariables()['foo'] = 'foo value';
$config->getVariables()['bar'] = 'bar value';

// Unset a variable
unset($config->getVariables()['bar']);
```

## Extend library

### Create an adapter

You can create your own adapter. Only implements `\Berlioz\Config\Adapter\AdapterInterface` interface.

This interface has only 3 methods:

- `AdapterInterface::getPriority(): int`
- `ConfigInterface::get(string $key, mixed $default = null): mixed`
- `ConfigInterface::has(string $key): bool`

Look at the existent adapters in the source code of the library for inspiration.

### Create a function

You can create your owns functions. Only implements `\Berlioz\Config\ConfigFunction\ConfigFunctionInterface` interface.

This interface has only 2 methods:

- `ConfigFunctionInterface::getName(): string`
- `ConfigFunctionInterface::execute(string $str): mixed`

Look at the existent functions in the source code of the library for inspiration.