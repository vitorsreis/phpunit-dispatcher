# Changelog

All notable changes to PHPUnit Dispatcher are documented here.

This project follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/)
and [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## 2.0.0 - Unreleased

Version 2 is a new implementation focused on one small application core shared by Composer and PHAR entrypoints. It
keeps the dispatcher contract while removing the old build-time dispatcher layers.

### Breaking Changes

- Replaced `src/dispatcher.php` and `src/builder.php` with `src/Application.php`, `bin/phpunit-dispatcher`, and
  `bin/phar`.
- Removed the checked-in `dist/phpunit-dispatcher.phar`; releases now generate `build/phar/phpunit-dispatcher.phar` in
  CI.
- Replaced the old `PUD\File`, `PUD\Http`, and `PUD\Logger` layout with PSR-4 classes directly under `src/`.
- Dispatcher-only flags remain under the `--pud-` namespace; normal PHPUnit arguments continue to be forwarded.

### Added

- Composer-installed `vendor/bin/phpunit-dispatcher` entrypoint.
- PHAR build automation with SHA-256 checksum generation.
- Shared application core used by Composer and PHAR execution.
- Explicit `--pud-version` and `--pud-help` commands.
- Platform-aware cache directories using `XDG_CACHE_HOME` on Unix and `LOCALAPPDATA`/`APPDATA` on Windows.
- Atomic cache writes, cache locks, offline mode, cache clearing, repository metadata, and explicit PHPUnit release
  selection.
- Safe forwarding of compact and separated PHP `-d` options before the PHPUnit PHAR.
- PHPUnit compatibility tests and a simple dispatcher smoke test run through PUD in CI.
- CI coverage for PHP 5.3 through PHP 8.5, with PHPUnit selected for each runtime.
- Contribution, security, code of conduct, issue, pull request, and release documentation.

### Changed

- PHPUnit releases are selected from the newest available patch release for the compatible major line using the upstream all-releases index.
- Downloaded PHARs are validated before being used and invalid cached files are discarded.
- HTTP downloads use verified TLS, bounded timeouts, a user agent, and cached response headers.
- PHAR release artifacts are built only during release automation and are excluded from source archives.
- Development dependencies resolve a PHPUnit version compatible with the active PHP runtime.
- PHPStan runs only on modern CI jobs because the analyzer does not support PHP 5.3.

### Security

- Cache writes are performed through temporary files and renamed atomically.
- Cache and PHAR operations are protected by file locks to avoid concurrent partial downloads.
- User-controlled PHPUnit release values are restricted to numeric semantic versions.
- PHAR paths and forwarded command arguments are shell-escaped before execution.
- TLS certificate and host verification are enabled for downloads.

### Removed

- `src/dispatcher.php` and `src/builder.php`.
- The checked-in `dist/` PHAR artifact.
- The old version-file based banner and build flow.

## 1.0.1 - 2026-02-19

### Added

- Updated PHP 8.4 to the PHPUnit 13 release line and added PHP 8.5 support through PHPUnit 13.

### Fixed

- Avoided the deprecated `curl_close()` call on PHP 8.5.
- Escaped the downloaded PHPUnit PHAR path when constructing the child command.

## 1.0.0 - 2025-05-05

### Added

- Initial PHPUnit PHAR dispatcher release.
- Automatic PHP-to-PHPUnit major-version selection from PHP 5.3 onward.
- Download and local caching of PHPUnit PHAR files.
- `--pud-phpunit-version`, `--pud-force-update`, and `--pud-verbose` options.
- Composer installation metadata and a source build script for the distributable PHAR.
