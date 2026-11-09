## Summary

- Describe the user-facing change.

## Contract changes

- [ ] CLI command or option
- [ ] forwarded PHPUnit or PHP runtime arguments
- [ ] cache or version resolution behavior
- [ ] PHAR/build/release behavior
- [ ] PHP/PHPUnit compatibility
- [ ] documentation only

## Checks

- [ ] `composer validate --strict`
- [ ] `composer lint`
- [ ] `composer test`
- [ ] `composer quality`
- [ ] `composer analyse` on a supported modern PHP version
- [ ] `composer build:phar` when packaging or release logic changed
- [ ] `php bin/phpunit-dispatcher tests/Integration/SimpleTest.php`

## Notes

- Compatibility or migration notes:
- Follow-up work, if intentionally deferred:
