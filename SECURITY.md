# Security Policy

## Supported Versions

Version 2 is the current release line. Security fixes target:

| Version | Supported                                    |
|---------|----------------------------------------------|
| `2.x`   | Yes                                          |
| `1.x`   | No planned fixes unless explicitly announced |

## Reporting a Vulnerability

Do not report security issues in public GitHub issues. Report them privately
through [GitHub Security Advisories](https://github.com/vitorsreis/phpunit-dispatcher/security/advisories/new). If
advisories are unavailable, contact the maintainer through the GitHub profile.

Include:

- affected dispatcher version or commit;
- PHP version and operating system;
- installation method: Composer, PHAR, or source checkout;
- exact command, arguments, cache path, and configuration needed to reproduce;
- impact, especially arbitrary command execution, unsafe file writes, path traversal, or credential exposure;
- any known workaround.

## Scope

Security-relevant areas include:

- command construction and argument escaping;
- execution of downloaded PHPUnit PHAR files;
- TLS downloads and release selection;
- cache locking, atomic writes, and cache path handling;
- path traversal or symlink escapes;
- Composer and PHAR packaging;
- forwarding PHP `-d` options to the child process.

## Response Process

The maintainer will triage valid reports, reproduce the issue, assess severity, prepare a fix, and publish release notes
when a fixed version is available. Public details may be delayed until users have had reasonable time to upgrade.

## Safe Harbor

Good-faith research is welcome when it avoids data destruction, service disruption, persistence, privacy violations, and
access to secrets that are not your own.
