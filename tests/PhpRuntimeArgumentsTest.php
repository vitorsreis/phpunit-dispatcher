<?php

use PUD\PhpRuntimeArguments;

class PhpRuntimeArgumentsTest extends PUDTestCase
{
    public function testExtractsIniDefinitionsBeforeTheScript()
    {
        $arguments = new PhpRuntimeArguments(array(
            PHP_BINARY,
            '-d',
            'memory_limit=512M',
            '-dxdebug.mode=off',
            '--some-php-flag',
            'bin/phpunit-dispatcher',
            'tests'
        ));

        $this->assertSame(array('-dmemory_limit=512M', '-dxdebug.mode=off'), $arguments->arguments());
    }

    public function testStopsAtTheFirstScriptToken()
    {
        $arguments = new PhpRuntimeArguments(array(
            PHP_BINARY,
            'bin/phpunit-dispatcher',
            '-dxdebug.mode=coverage'
        ));

        $this->assertSame(array(), $arguments->arguments());
    }

    public function testCommandMergesInheritedAndExplicitDefinitionsByName()
    {
        $command = PUD\Application::buildCommand(
            '/tmp/phpunit.phar',
            array('-d', 'memory_limit=1G', '--filter', 'ExampleTest'),
            array('-dmemory_limit=512M', '-dxdebug.mode=off')
        );

        $this->assertContainsCompat(escapeshellarg('-dmemory_limit=1G'), $command);
        $this->assertContainsCompat(escapeshellarg('-dxdebug.mode=off'), $command);
        $this->assertNotContainsCompat(escapeshellarg('-dmemory_limit=512M'), $command);
        $this->assertLessThan(
            strpos($command, escapeshellarg('/tmp/phpunit.phar')),
            strpos($command, escapeshellarg('-dmemory_limit=1G'))
        );
    }
}
