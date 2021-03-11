# Berlioz Configuration

[![Latest Version](https://img.shields.io/packagist/v/berlioz/config.svg?style=flat-square)](https://github.com/BerliozFramework/Config/releases)
[![Software license](https://img.shields.io/github/license/BerliozFramework/Config.svg?style=flat-square)](https://github.com/BerliozFramework/Config/blob/1.x/LICENSE)
[![Build Status](https://img.shields.io/travis/com/BerliozFramework/Config/1.x.svg?style=flat-square)](https://travis-ci.com/BerliozFramework/Config)
[![Quality Grade](https://img.shields.io/codacy/grade/f290647a1f5143ec8299ecea9b83d6b1/1.x.svg?style=flat-square)](https://www.codacy.com/manual/BerliozFramework/Config)
[![Total Downloads](https://img.shields.io/packagist/dt/berlioz/config.svg?style=flat-square)](https://packagist.org/packages/berlioz/config)

**Berlioz Configuration** is a PHP library to manage your configuration files.

## Installation

### Composer

You can install **Berlioz Configuration** with [Composer](https://getcomposer.org/), it's the recommended installation.

```bash
$ composer require berlioz/config
```

### Dependencies

* **PHP** ^7.1 || ^8.0
* PHP libraries:
  * **ext-json**
* Packages:
  * **berlioz/helpers**


## Usage

### Create configuration object

You can create configuration like this:
```php
// Using files
$config = new JsonConfig('/path/of-project/config/config.json', true);
$config = new ExtendedJsonConfig('/path/of-project/config/config.json', true);

// Using data
$config = new JsonConfig('{"config": "test"}');
$config = new ExtendedJsonConfig('{"config": "test"}');
```

Second parameter of constructor is if the first parameter is an URL.

### Get value of key

Configuration file:
```json
{
  "var1": "value1",
  "var2": {
    "var3": "value3"
  }
}
```

To get value, you must do like this:
```php
$config->get('var1'); // returns string 'value1'
$config->get('var2'); // returns array ['var3' => 'value3']
$config->get('var2.var3'); // returns 'value3'
$config->get('var3'); // returns NULL
$config->get('var3', true); // returns TRUE (default value given)
```

If you get an unknown value, the method return the default value given in second parameter else **NULL**.

You can also test if a key exists, like this:
```php
$config->has('var1'); // returns true
$config->has('var2'); // returns true
$config->has('var4'); // returns false
```

### Variables

In values of JSON keys, you can add this syntax to use variables:
`%var1.var2%`,
that's get key **var1.var2** in replacement of value.

Some variables are available by default:

- **php_version**: the value of constant PHP_VERSION
- **php_version_id**: the value of constant PHP_VERSION_ID
- **php_major_version**: the value of constant PHP_MAJOR_VERSION
- **php_minor_version**: the value of constant PHP_MINOR_VERSION
- **php_release_version**: the value of constant PHP_RELEASE_VERSION
- **php_sapi**: the value of constant PHP_SAPI
- **system_os**: the value of constant PHP_OS
- **system_os_family**: the value of constant PHP_OS_FAMILY

You can also define your own variables with the methods:
- `setVariable(string $name, mixed $value)`
- `setVariables(array $variables)`

**WARNING**: Priority is given to the user defined variable in the config object instead of JSON path.

## Extended JSON format

We created an extended format of the JSON format.
Just to do actions like include or extends JSON files.

### Syntax

* Include another file: `%include:filename.json%`
* Extends a file: `%extends:filename.json, filename2.json, filename3.json%`
* Replace by an env variable: `%env:VAR_NAME%`
* Replace by a constant: `%const:VAR_NAME%` or `%constant:VAR_NAME%` 
* Allow inline comments : `// My comment` (comment must be alone on a line) 

You can define your own actions with static method `ExtendedJsonConfig::addAction(string $name, callable $callback)`.

### Extends configurations files

You can extends the current configuration file with another with special key `@extends`:
```json
{
  // Extends
  "@extends": "another.json",
  // Keys
  "key": "value"
}
```

### Example

File **config.json**:

```json
{
  "@extends": "config.another.json",
  "var1": "value1",
  "var2": {
    "var3": "value3"
  },
  "var4": "%include:config3.json%",
  "var5": "%extends:config3.json, config2.json%"
}
```

File **config.another.json**:

```json
{
  "var.another": "value",
  "var1": "valueX"
}
```

File **config2.json**:

```json
{
  "var6": "value1",
  "var7": false
}
```

File **config3.json**:

```json
{
  "var7": true
}
```

The final config file is:

```json
{
  "var.another": "value",
  "var1": "value1",
  "var2": {
    "var3": "value3"
  },
  "var4": {
    "var7": true
  },
  "var5": {
    "var6": "value1",
    "var7": false
  }
}
```
