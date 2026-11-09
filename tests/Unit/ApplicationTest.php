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
