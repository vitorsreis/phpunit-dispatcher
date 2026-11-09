<?php

use PUD\Application;

class ApplicationTest extends PUDTestCase
{
    public function testVersionOptionIsAvailableWithoutNetwork()
    {
        $this->expectOutputString("2.0.0\n");
        $this->assertSame(0, Application::run(array('phpunit-dispatcher', '--pud-version')));
    }

    public function testHelpOptionIsAvailableWithoutNetwork()
    {
        $this->expectOutputRegex('/Usage: phpunit-dispatcher/');
        $this->assertSame(0, Application::run(array('phpunit-dispatcher', '--pud-help')));
    }

    public function testInvalidVersionIsRejected()
    {
        $this->assertSame(1, Application::run(array('phpunit-dispatcher', '--pud-phpunit-version=../latest')));
    }

    public function testOfflineModeFailsWhenReleaseIsNotCached()
    {
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'pud-app-' . uniqid('', true);
        $code = Application::run(array(
            'phpunit-dispatcher',
            '--pud-cache-dir=' . $directory,
            '--pud-phpunit-version=9.6.26',
            '--pud-offline'
        ));
        $this->assertSame(1, $code);
        PUD\Cache::remove($directory);
    }

    public function testOfflineModeUsesMappingButRejectsAnInvalidCachedPhar()
    {
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'pud-app-' . uniqid('', true);
        PUD\Cache::ensure($directory);
        file_put_contents($directory . DIRECTORY_SEPARATOR . 'mapping.json', json_encode(array('85' => '13.4.6')));
        file_put_contents($directory . DIRECTORY_SEPARATOR . 'phpunit-13.4.6.phar', 'not a phar');
        $code = Application::run(array(
            'phpunit-dispatcher',
            '--pud-cache-dir=' . $directory,
            '--pud-offline'
        ));
        $this->assertSame(1, $code);
        $this->assertFileExists($directory . DIRECTORY_SEPARATOR . 'mapping.json');
        PUD\Cache::remove($directory);
    }

    public function testClearCacheRemovesStaleFilesBeforeOfflineRun()
    {
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'pud-app-' . uniqid('', true);
        PUD\Cache::ensure($directory);
        file_put_contents($directory . DIRECTORY_SEPARATOR . 'stale', 'data');
        $code = Application::run(array(
            'phpunit-dispatcher',
            '--pud-cache-dir=' . $directory,
            '--pud-clear-cache',
            '--pud-phpunit-version=9.6.26',
            '--pud-offline'
        ));
        $this->assertSame(1, $code);
        $this->assertFalse(is_file($directory . DIRECTORY_SEPARATOR . 'stale'));
        PUD\Cache::remove($directory);
    }

    public function testPhpIniArgumentsArePlacedBeforeThePhar()
    {
        $command = Application::buildCommand('/tmp/phpunit.phar', array(
            '-dmemory_limit=512M',
            '-d',
            'xdebug.mode=off',
            '--filter',
            'ExampleTest',
            '--pud-verbose'
        ));

        $pharPosition = strpos($command, escapeshellarg('/tmp/phpunit.phar'));
        $memoryPosition = strpos($command, escapeshellarg('-dmemory_limit=512M'));
        $xdebugPosition = strpos($command, escapeshellarg('xdebug.mode=off'));
        $filterPosition = strpos($command, escapeshellarg('--filter'));

        $this->assertLessThan($pharPosition, $memoryPosition);
        $this->assertLessThan($pharPosition, $xdebugPosition);
        $this->assertGreaterThan($pharPosition, $filterPosition);
        $this->assertNotContainsCompat('--pud-verbose', $command);
    }
}
