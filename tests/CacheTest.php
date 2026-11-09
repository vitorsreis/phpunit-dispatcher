<?php

use PUD\Cache;

class CacheTest extends PUDTestCase
{
    private $directory;

    private function directory()
    {
        if ($this->directory === null) {
            $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'pud-test-' . uniqid('', true);

            Cache::ensure($directory);

            register_shutdown_function(function () use ($directory) {
                Cache::remove($directory);
            });

            $this->directory = $directory;
        }

        return $this->directory;
    }

    public function testAtomicJsonWriteCanBeReadBack()
    {
        $file = $this->directory() . DIRECTORY_SEPARATOR . 'mapping.json';
        $this->assertTrue(Cache::writeAtomic($file, json_encode(array('80' => '9.6.26'))));
        $this->assertSame(array('80' => '9.6.26'), Cache::readJson($file));
    }

    public function testInvalidJsonReturnsAnEmptyMapping()
    {
        $file = $this->directory() . DIRECTORY_SEPARATOR . 'invalid.json';
        file_put_contents($file, '{invalid');
        $this->assertSame(array(), Cache::readJson($file));
    }

    public function testCacheDirectoryOverrideIsNormalized()
    {
        $this->assertSame('/tmp/pud-cache', Cache::directory('/tmp/pud-cache/'));
    }

    public function testClearKeepsTheCacheRoot()
    {
        $directory = $this->directory();
        file_put_contents($directory . DIRECTORY_SEPARATOR . 'stale', 'data');
        $this->assertTrue(Cache::clear($directory));
        $this->assertTrue(is_dir($directory));
        $this->assertFalse(is_file($directory . DIRECTORY_SEPARATOR . 'stale'));
    }

    public function testMissingFilesAreSafeToRead()
    {
        $directory = $this->directory();
        $this->assertFalse(Cache::read($directory . DIRECTORY_SEPARATOR . 'missing'));
        $this->assertSame(array(), Cache::readJson($directory . DIRECTORY_SEPARATOR . 'missing.json'));
    }

    public function testRemoveDeletesAnIsolatedCacheDirectory()
    {
        $directory = $this->directory();
        $nested = $directory . DIRECTORY_SEPARATOR . 'nested';
        $this->assertTrue(Cache::ensure($nested));
        file_put_contents($nested . DIRECTORY_SEPARATOR . 'file', 'data');
        $this->assertTrue(Cache::remove($directory));
        $this->assertFalse(is_dir($directory));
    }

    public function testWithLockRunsCallbackAndReleasesTheHandle()
    {
        $lock = $this->directory() . DIRECTORY_SEPARATOR . 'cache.lock';

        $this->assertSame('done', Cache::withLock($lock, function () {
            return 'done';
        }));

        $this->assertFileExists($lock);
    }
}
