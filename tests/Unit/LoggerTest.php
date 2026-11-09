<?php

use PUD\Logger;

class LoggerTest extends PUDTestCase
{
    public function testInvalidLogLevelIsRejected()
    {
        $this->expectExceptionCompat('InvalidArgumentException');
        Logger::setLevel('invalid');
    }

    public function testValidLogLevelsCanBeSelected()
    {
        Logger::setLevel('trace');
        Logger::setLevel('success');
        Logger::setLevel('error');
        $this->assertTrue(true);
    }
}
