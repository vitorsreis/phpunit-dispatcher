# PHPUnit Dispatcher

[![CI](https://github.com/vitorsreis/phpunit-dispatcher/actions/workflows/ci.yml/badge.svg)](https://github.com/vitorsreis/phpunit-dispatcher/actions/workflows/ci.yml)
[![Latest release](https://img.shields.io/github/v/release/vitorsreis/phpunit-dispatcher)](https://github.com/vitorsreis/phpunit-dispatcher/releases)
[![PHP versions](https://img.shields.io/packagist/php-v/vitorsreis/phpunit-dispatcher)](https://packagist.org/packages/vitorsreis/phpunit-dispatcher)
[![Downloads](https://img.shields.io/packagist/dt/vitorsreis/phpunit-dispatcher)](https://packagist.org/packages/vitorsreis/phpunit-dispatcher)
[![License](https://img.shields.io/github/license/vitorsreis/phpunit-dispatcher)](LICENSE)

PHPUnit Dispatcher (PUD) selects the newest PHPUnit PHAR compatible with the PHP runtime that launches it. It keeps
downloaded releases in a local cache and forwards normal PHPUnit arguments unchanged.

It supports PHP 5.3 through PHP 8.5, so one project command can run the appropriate PHPUnit release on legacy and
current environments.

## Quick start

Install it in a project:

```bash
composer require --dev vitorsreis/phpunit-dispatcher
vendor/bin/phpunit-dispatcher tests
```

Or download a release PHAR:

```bash
php phpunit-dispatcher.phar tests
```

The first run downloads the compatible PHPUnit PHAR. Later runs reuse the cache.

## Compatibility

| PHP runtime | PHPUnit line |
|-------------|--------------|
| 5.3–5.5     | 4            |
| 5.6         | 5            |
| 7.0         | 6            |
| 7.1         | 7            |
| 7.2         | 8            |
| 7.3–8.0     | 9            |
| 8.1         | 10           |
| 8.2         | 11           |
| 8.3         | 12           |
| 8.4–8.5     | 13           |

The dispatcher supports the full PHP range above. PHPUnit itself may impose additional requirements for a selected
release, such as extensions required by the test suite.

## Usage

PUD accepts regular PHPUnit options and forwards them to the selected PHAR:

```bash
php phpunit-dispatcher.phar tests
php phpunit-dispatcher.phar --filter UserTest tests
php phpunit-dispatcher.phar --coverage-text tests
```

Dispatcher options use the `--pud-` prefix and are removed before PHPUnit starts:

```bash
php phpunit-dispatcher.phar --pud-version
php phpunit-dispatcher.phar --pud-help
php phpunit-dispatcher.phar --pud-phpunit-version=9.6.26 tests
php phpunit-dispatcher.phar --pud-force-update tests
php phpunit-dispatcher.phar --pud-offline tests
php phpunit-dispatcher.phar --pud-clear-cache tests
php phpunit-dispatcher.phar --pud-verbose tests
```

PHP runtime options work both before the dispatcher and after it. They are placed before the child PHAR so they
configure the PHP process that runs PHPUnit:

```bash
php -d memory_limit=1G phpunit-dispatcher.phar tests
php phpunit-dispatcher.phar -d xdebug.mode=coverage tests
php phpunit-dispatcher.phar -dmemory_limit=1G tests
```

## Cache and offline mode

The default cache is `~/.cache/phpunit-dispatcher` on Unix-like systems and `%LOCALAPPDATA%/phpunit-dispatcher` on
Windows. Set `PUD_CACHE_DIR` or use `--pud-cache-dir=PATH` to choose another location.

Use offline mode when the repository metadata and requested PHAR are already cached:

```bash
php phpunit-dispatcher.phar --pud-offline tests
```

`--pud-clear-cache` removes cached metadata and releases before the next run. `--pud-force-update` refreshes the release
repository. An explicit version must have the `X.Y.Z` form.

## Development

```bash
composer install
composer validate --strict
composer quality
composer build:phar
php build/phar/phpunit-dispatcher.phar --pud-version
```

The same application core powers the Composer binary and the PHAR. `tests/SimpleTest.php` is intentionally minimal and
is executed through PUD on every PHP version in CI. The full PHPUnit suite runs with the version resolved for each
supported runtime.

PHPStan is an optional modern-runtime check:

```bash
composer require --dev phpstan/phpstan:^2.0
composer analyse
```

Release builds are generated in `build/phar/` and include `phpunit-dispatcher.phar.sha256`. Do not commit generated
builds, `vendor/`, caches, or `composer.lock`.

## Security

Downloads use TLS certificate and host verification. Verify the SHA-256 checksum published beside a release PHAR before
executing it. Report vulnerabilities privately using [SECURITY.md](SECURITY.md).

## Contributing

Read [CONTRIBUTING.md](CONTRIBUTING.md) before opening a pull request. The project also includes
a [Code of Conduct](CODE_OF_CONDUCT.md).

## License

Released under the [MIT license](LICENSE).
