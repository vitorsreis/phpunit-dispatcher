# Contributing

## Development Principles

- Keep changes small, focused, and reviewable.
- Preserve support for PHP 5.3 through PHP 8.5; avoid syntax and APIs unavailable on PHP 5.3.
- Keep the Composer entrypoint, PHAR entrypoint, cache behavior, and forwarded PHPUnit arguments aligned.
- Add a regression test for behavior changes before changing the implementation.
- Keep documentation, workflow checks, and release behavior aligned with the code.

## Local Setup

```bash
composer install
composer validate --strict
composer quality
composer build:phar
```

`composer.lock` is intentionally not committed because this is a Composer library. Dependency resolution must remain
possible on every supported PHP version.

## Quality Commands

```bash
composer lint
composer test
composer quality
composer build:phar
php bin/phpunit-dispatcher tests/Integration/SimpleTest.php
```

PHPStan is an optional development dependency because it does not support the complete PHP 5.3–8.5 matrix. On PHP 8.1 or
newer:

```bash
composer require --dev phpstan/phpstan:^2.0
composer analyse
```

`tests/Integration/SimpleTest.php` is intentionally small and is run through the dispatcher in CI on every supported PHP version.

## Tests

The test suite is split into `tests/Unit` for isolated behavior and `tests/Integration` for end-to-end execution through the dispatcher.

- Keep tests compatible with PHPUnit 4 through PHPUnit 13.
- Test public behavior rather than private implementation details.
- Cover version selection, cache and lock behavior, HTTP failures, argument forwarding, PHAR packaging, and CLI options
  when they change.
- Keep network-dependent tests deterministic; use explicit cache directories and reusable fixtures where possible.
- Update the compatibility matrix when a supported PHP or PHPUnit release changes.

## Documentation

Update documentation when changing:

- commands, options, or forwarded arguments;
- PHP/PHPUnit compatibility;
- cache locations or update behavior;
- PHAR build or release behavior;
- error messages or exit codes;
- security-sensitive execution or file handling.

## Pull Requests

Include:

- a user-facing summary;
- tests and quality checks run;
- documentation or workflow changes;
- compatibility impact;
- intentional follow-up work, only when deliberately deferred.

## Commit Style

Use Conventional Commits:

```text
feat(cache): add cache validation
fix(cli): preserve PHP ini arguments
chore(ci): test the dispatcher across supported PHP versions
```

Do not commit generated PHAR files, `vendor/`, coverage, caches, or local configuration.

## Releases

Releases are created from `vX.Y.Z` tags. CI validates the package, builds the PHAR, writes its SHA-256 checksum, and
publishes both assets. Update `CHANGELOG.md` before tagging.
