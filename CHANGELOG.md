# Change Log

All notable changes to this project will be documented in this file. This project adheres
to [Semantic Versioning] (http://semver.org/). For change log format,
use [Keep a Changelog] (http://keepachangelog.com/).

## [2.0.0-alpha1]

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