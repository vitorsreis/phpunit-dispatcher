# PHPUnit Dispatcher

[![Latest Version](https://img.shields.io/github/release/vitorsreis/phpunit-dispatcher.svg?style=flat-square)](https://github.com/vitorsreis/phpunit-dispatcher/releases)
[![Total Downloads](https://img.shields.io/packagist/dt/vitorsreis/phpunit-dispatcher.svg?style=flat-square)](https://packagist.org/packages/vitorsreis/phpunit-dispatcher)
[![License](https://img.shields.io/github/license/vitorsreis/phpunit-dispatcher.svg?style=flat-square)](LICENSE)
[![PHP Version](https://img.shields.io/packagist/php-v/vitorsreis/phpunit-dispatcher.svg?style=flat-square)](composer.json)

A tool that automatically selects and runs the appropriate [PHPUnit](https://github.com/sebastianbergmann/phpunit) PHAR version based on your PHP version.

## Description

PHPUnit Dispatcher is a smart wrapper for PHPUnit that automatically selects the correct PHAR version based on your current PHP version. This eliminates the need to manually manage different PHPUnit versions for different PHP environments.

## Features

- Automatically detects your PHP version
- Selects the appropriate PHPUnit version
- Runs PHPUnit with all your specified arguments
- Easy to use - just run it like you would run PHPUnit
- Lightweight - no additional dependencies required
- Support simultaneous processes with same or different PHPUnit versions
- Caching mechanism for faster subsequent runs

## Installation

### Using Composer

```bash
composer require --dev vitorsreis/phpunit-dispatcher
```

After installation, the phpunit-dispatcher.phar file will be available in `vendor/bin/phpunit-dispatcher.phar`.

### Manual Installation

Download the latest release from the [releases page](https://github.com/vitorsreis/phpunit-dispatcher/releases)

## Usage

Use it exactly like you would use PHPUnit:

```bash
php phpunit-dispatcher.phar [options] <directory|file> ...
```

For example:

```bash
php phpunit-dispatcher.phar --version

php phpunit-dispatcher.phar tests/
```

### Cache and Update

The first time you run the PHPUnit Dispatcher, it will download the appropriate PHPUnit PHAR version for your PHP version and store in cache. This allows for faster subsequent runs. You can force the PHPUnit version update using the `--pud-force-update` argument:

```bash
php phpunit-dispatcher.phar --pud-force-update
```

### Specifying PHPUnit Version Manually

You can also manually specify the PHPUnit version to be used with the `--pud-phpunit-version` argument:

```bash
php phpunit-dispatcher.phar --pud-phpunit-version=9.5.0
```

Or configure the PHP version mapping by editing the `phpunit-dispatcher/mapping.json`

## Building from Source

To build the project from source:

1. Clone the repository
2. Build the Phar file:
```bash
composer run-script build
```

### Verbose Mode

To enable verbose mode with additional logs during execution, use the `--pud-verbose` argument:

```bash
php phpunit-dispatcher.phar --pud-verbose
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Author

- Vitor Reis (vitor@d5w.com.br)
