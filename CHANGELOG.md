# Change Log

All notable changes to this project will be documented in this file. This project adheres
to [Semantic Versioning] (http://semver.org/). For change log format,
use [Keep a Changelog] (http://keepachangelog.com/).

## [2.0.0-beta4] - In progress

### Changed

- Use constants to define encapsulation characters of functions 

### Fixed

- Fix tests

## [2.0.0-beta3] - 2021-06-07

### Changed

- Bump dependency version of `berlioz/helpers` package to 1.2

### Fixed

- Fixed `Config::getArrayCopy()` with no config
- Fixed reverse of configs in `Config::getArrayCopy()`

## [2.0.0-beta2] - 2021-04-14

### Added

- New `YamlAdapter` adapter
- Add parameter `$compiled` to `Config::getArrayCopy()` to get the compiled version

### Fixed

- Fixed multiple function calls in a value, or concatenation of function result and string
- Fixed way of merge from multiple configuration file

## [2.0.0-beta1] - 2021-04-07

### Added

- New `ConfigInterface::getArrayCopy()` method to have an array representation of configuration
- New `ConfigBridgeAdapter` adapter

### Changed

- `Config::addConfig()` now only accepts `AdapterInterface`

### Fixed

- Fixed null returned value
- Fixed returning type of ConfigFunction to mixed
- Fixed default value returned in `AbstractAdapter`

## [2.0.0-alpha2] - 2021-03-12

### Changed

- Allow a string at the configuration parameter in ArrayAdapter, to include a PHP file that returns an array

## [2.0.0-alpha1] - 2021-03-11

### Added

- Adapter concept
- Config object to manage adapters
- Dependency with `colinodell/json5` library to parse JSON5 syntax
- New adapter IniAdapter (INI string and files)
- New adapter ArrayAdapter (PHP array)

### Changed

- Refactoring
- Bump compatibility to PHP 8 minimum
- Actions replaced by functions
- Encapsulation of functions
- Functions must be alone in value of configuration key

### Removed

- Remove usage of `@extends` spacial key in configuration
- Remove merging of configurations, replaced by multiple config objects prioritized

## [1.2.0] - 2020-11-05

### Added

- PHP 8 compatibility

## [1.1.1] - 2020-09-23

### Changed

- Fix variable replacement by null with empty string

## [1.1.0] - 2020-04-17

### Added

- New `const` action to get constant value

## [1.0.0] - 2020-02-17

First version