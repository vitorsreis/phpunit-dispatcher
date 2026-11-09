<?php

use PUD\VersionResolver;

class VersionResolverTest extends PUDTestCase
{
    public function testSupportedPhpVersionsResolveToExpectedPhpunitMajorVersions()
    {
        $this->assertSame('4', VersionResolver::phpunitMajorVersion('5.3.0'));
        $this->assertSame('5', VersionResolver::phpunitMajorVersion('5.6.40'));
        $this->assertSame('9', VersionResolver::phpunitMajorVersion('8.0.30'));
        $this->assertSame('13', VersionResolver::phpunitMajorVersion('8.5.0'));
    }

    public function testReleaseValidationRejectsPathLikeValues()
    {
        $this->assertTrue(VersionResolver::isValidRelease('9.6.26'));
        $this->assertFalse(VersionResolver::isValidRelease('../latest'));
        $this->assertFalse(VersionResolver::isValidRelease('9.6'));
    }

    public function testLatestReleaseMappingSelectsHighestPatchPerMajor()
    {
        $mapping = VersionResolver::latestByMajor(
            '<a href="phpunit-9.5.0.phar">9.5</a><a href="phpunit-9.6.26.phar">9.6</a><a href="phpunit-10.5.2.phar">10</a>'
        );

        $this->assertSame('9.6.26', $mapping['9']);
        $this->assertSame('10.5.2', $mapping['10']);
    }

    public function testDispatcherVersionIsV2()
    {
        $this->assertSame('2.0.0', VersionResolver::DISPATCHER_VERSION);
    }

    public function testUnsupportedPhpAndInvalidRepositoryAreRejected()
    {
        $this->assertFalse(VersionResolver::phpunitMajorVersion('5.2.0'));
        $this->assertSame(array(), VersionResolver::latestByMajor('no releases here'));
    }
}
