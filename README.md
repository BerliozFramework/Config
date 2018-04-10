# Berlioz Configuration

**Berlioz Configuration** is a PHP library to manage your configuration files.


## Installation

### Composer

You can install **Berlioz Configuration** with [Composer](https://getcomposer.org/), it's the recommended installation.

```bash
$ composer require berlioz/config
```

### Dependencies

* **PHP** >= 7.1
* Packages:
  * **berlioz/utils**
  * **psr/log**


## Usage

### Create configuration object

You can create configuration like this:
```php
$config = new JsonConfig('/root/directory/of-project', '/config/config.json');
$config = new ExtendedJsonConfig('/root/directory/of-project', '/config/config.json');
```

Second parameter of constructor is the relative path of config file starting to the given root directory.

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
$config->get('var1.var3'); // returns 'value3'
```

If you get an unknown value, the method thrown an exception: **\Berlioz\Config\NotFoundException**.

You can also test if a key exists, like this:
```php
$config->has('var1'); // returns true
$config->has('var2'); // returns true
$config->has('var4'); // returns false
```


## Extended JSON format

We created an extended format of the JSON format. Just to include, extends or uses variables in standard JSON format.

### Syntax

In values of JSON keys, you can add this syntax to use variables:
`~~var1.var2~~`,
that's get key **var1.var2** in replacement of value.

You can also do some actions:
* Include another file: `~~include:filename.json~~`
* Extends files: `~~extends:filename.json, filename2.json, filename3.json~~`
* Call special variable: `~~special:directory_root~~`

### Available specials variables

- **directory_root**: the directory root given to the constructor
- **php_version**: the value of constant PHP_VERSION
- **php_version_id**: the value of constant PHP_VERSION_ID
- **php_major_version**: the value of constant PHP_MAJOR_VERSION
- **php_minor_version**: the value of constant PHP_MINOR_VERSION
- **php_release_version**: the value of constant PHP_RELEASE_VERSION
- **php_sapi**: the value of constant PHP_SAPI
- **system_os**: the value of constant PHP_OS
- **system_os_family**: the value of constant PHP_OS_FAMILY

You can also define another special variables with methods:
- `setSpecialVariable(string $name, mixed $value)`
- `setSpecialVariables(array $variables)`

### Example

File **config.json**:

```json
{
  "var1": "value1",
  "var2": {
    "var3": "value3"
  },
  "var4": "~~include:config3.json~~",
  "var5": "~~extends:config3.json, config2.json~~"
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
