# PHPUnit Dispatcher

[![CI](https://github.com/vitorsreis/phpunit-dispatcher/actions/workflows/ci.yml/badge.svg)](https://github.com/vitorsreis/phpunit-dispatcher/actions/workflows/ci.yml)
[![Latest release](https://img.shields.io/github/v/release/vitorsreis/phpunit-dispatcher)](https://github.com/vitorsreis/phpunit-dispatcher/releases)
[![PHP versions](https://img.shields.io/packagist/php-v/vitorsreis/phpunit-dispatcher)](https://packagist.org/packages/vitorsreis/phpunit-dispatcher)
[![Downloads](https://img.shields.io/packagist/dt/vitorsreis/phpunit-dispatcher)](https://packagist.org/packages/vitorsreis/phpunit-dispatcher)
[![License](https://img.shields.io/github/license/vitorsreis/phpunit-dispatcher)](LICENSE)

<p align="center">
  <strong>One PHPUnit command for every PHP version.</strong>
</p>

PHPUnit Dispatcher keeps your test command stable while your PHP versions change. It detects the PHP runtime, chooses a compatible PHPUnit release, downloads it when needed, and reuses it from the local cache on the next run.

That means one project can test PHP 5.3, PHP 7, and PHP 8.5 without checking different PHPUnit versions into the project or maintaining separate scripts for each environment.

## Why PHPUnit Dispatcher

- **One command everywhere:** use the same `vendor/bin/phpunit-dispatcher tests` command locally and in CI.
- **Legacy and current PHP:** support PHP 5.3 through PHP 8.5 with the matching PHPUnit line.
- **No version maintenance:** PUD selects the newest compatible patch release automatically.
- **Fast repeat runs:** downloaded PHARs and release metadata stay in a local cache.
- **Composer or PHAR:** install it in a project or download a standalone release.
- **Safe forwarding:** normal PHPUnit options and PHP `-d` settings reach the PHPUnit process unchanged.

## Quick start

Install it in a project:

```bash
composer require --dev vitorsreis/phpunit-dispatcher
vendor/bin/phpunit-dispatcher tests
```

Or download the [latest PHAR release](https://github.com/vitorsreis/phpunit-dispatcher/releases):

```bash
php phpunit-dispatcher.phar tests
```

The first run downloads the compatible PHPUnit PHAR. Subsequent runs use the cached release automatically.

## Compatibility

| PHP runtime | PHPUnit line |
| --- | --- |
| 5.3–5.5 | 4 |
| 5.6 | 5 |
| 7.0 | 6 |
| 7.1 | 7 |
| 7.2 | 8 |
| 7.3–8.0 | 9 |
| 8.1 | 10 |
| 8.2 | 11 |
| 8.3 | 12 |
| 8.4–8.5 | 13 |

## Daily usage

Use PHPUnit exactly as you normally would:

```bash
vendor/bin/phpunit-dispatcher tests
vendor/bin/phpunit-dispatcher --filter UserTest tests
vendor/bin/phpunit-dispatcher --coverage-text tests
```

PUD options use the `--pud-` prefix:

```bash
vendor/bin/phpunit-dispatcher --pud-version
vendor/bin/phpunit-dispatcher --pud-help
vendor/bin/phpunit-dispatcher --pud-phpunit-version=9.6.38 tests
vendor/bin/phpunit-dispatcher --pud-force-update tests
vendor/bin/phpunit-dispatcher --pud-clear-cache tests
vendor/bin/phpunit-dispatcher --pud-verbose tests
```

`--pud-force-update` refreshes the release index and selects the newest compatible release. Without it, PUD first uses the local mapping and cached PHAR, then downloads only what is missing.

PHP runtime settings can be passed before or after the dispatcher:

```bash
php -d memory_limit=1G vendor/bin/phpunit-dispatcher tests
vendor/bin/phpunit-dispatcher -d xdebug.mode=coverage tests
```

## Cache

PUD uses `~/.cache/phpunit-dispatcher` on Unix-like systems and `%LOCALAPPDATA%/phpunit-dispatcher` on Windows. Set `PUD_CACHE_DIR` or use `--pud-cache-dir=PATH` to choose another location.

Use `--pud-clear-cache` to remove cached releases and metadata. PUD will rebuild the cache automatically on the next run.

## Development

```bash
composer install
composer quality
composer analyse
composer build:phar
php build/phar/phpunit-dispatcher.phar --pud-version
```

The test suite is split into `tests/Unit` and `tests/Integration`. The small integration smoke test is executed through PUD on every supported PHP version in CI.

PHPStan runs at its maximum level on modern PHP and uses a committed baseline for legacy code that must remain valid on PHP 5.3. PHPUnit is configured with separate Unit and Integration suites while keeping the configuration compatible with PHPUnit 4 through PHPUnit 13.

Release builds are generated in `build/phar/` with a SHA-256 checksum. Generated builds, `vendor/`, caches, and `composer.lock` are not committed.

## Security

Downloads use TLS certificate and host verification. Verify the SHA-256 checksum published beside a release PHAR before executing it. Report vulnerabilities privately using [SECURITY.md](SECURITY.md).

## Contributing

Read [CONTRIBUTING.md](CONTRIBUTING.md) before opening a pull request. The project also includes a [Code of Conduct](CODE_OF_CONDUCT.md).

## License

Released under the [MIT license](LICENSE).
