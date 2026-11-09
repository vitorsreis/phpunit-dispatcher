<?php

require dirname(__DIR__) . '/vendor/autoload.php';

if (class_exists('PHPUnit\\Framework\\TestCase')) {
    abstract class PUDTestCase extends \PHPUnit\Framework\TestCase
    {
        protected function expectExceptionCompat($class)
        {
            if (method_exists($this, 'expectException')) {
                $this->expectException($class);
                return;
            }

            $this->setExpectedException($class);
        }

        protected function assertContainsCompat($needle, $haystack)
        {
            if (method_exists($this, 'assertStringContainsString')) {
                $this->assertStringContainsString($needle, $haystack);
                return;
            }

            $this->assertContains($needle, $haystack);
        }

        protected function assertNotContainsCompat($needle, $haystack)
        {
            if (method_exists($this, 'assertStringNotContainsString')) {
                $this->assertStringNotContainsString($needle, $haystack);
                return;
            }

            $this->assertNotContains($needle, $haystack);
        }
    }
} else {
    abstract class PUDTestCase extends PHPUnit_Framework_TestCase
    {
        protected function expectExceptionCompat($class)
        {
            $this->setExpectedException($class);
        }

        protected function assertContainsCompat($needle, $haystack)
        {
            $this->assertContains($needle, $haystack);
        }

        protected function assertNotContainsCompat($needle, $haystack)
        {
            $this->assertNotContains($needle, $haystack);
        }
    }
}
