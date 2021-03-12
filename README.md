# Spryker Codeception Module
[![Build Status](https://travis-ci.org/fond-of-codeception/spryker.svg?branch=master)](https://travis-ci.org/fond-of-codeception/spryker)
[![PHP from Packagist](https://img.shields.io/packagist/php-v/fond-of-codeception/spryker.svg)](https://php.net/)
[![Packagist](https://img.shields.io/packagist/l/fond-of-codeception/spryker.svg)](https://packagist.org/packages/fond-of-codeception/spryker)

This modules allows developers to test spryker modules isolated.

## Installation

1. Add the package to `composer.json`:
    ```
    composer require --dev fond-of-codeception/spryker
    ```

2. Enable module in `codeception.yml`:
    ``` yml
    ...
    modules:
      enabled:
        - ...
        - \FondOfCodeception\Module\Spryker
      config:
        \FondOfCodeception\Module\Spryker:
            generate_transfer: true|false # Default is true
            generate_map_classes: true|false # Default is true
            generate_propel_classes: true|false # Default is true
            supported_source_identifiers: [string] # Default is ['page']
    ...
    ```

## Features

* Generate (entity)transfer classes
* Generate propel classes
* Generate map classes
* Initialize environment (constants like APPLICATION_ROOT_DIR will be created)

## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

## Versioning

We use [SemVer](http://semver.org/) for versioning. For the versions available, see the [tags on this repository](https://github.com/fond-of/codeception-spryker/tags).

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details

## Authors

* **Daniel Rose** - [daniel-rose](https://github.com/daniel-rose)
