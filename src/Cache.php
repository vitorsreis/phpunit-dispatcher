<?php

namespace PUD;

use Exception;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;

class Cache
{
    public static function directory($override = null)
    {
        if ($override) {
            return rtrim($override, '/\\');
        }

        $configured = getenv('PUD_CACHE_DIR');
        if ($configured) {
            return rtrim($configured, '/\\');
        }

        if (DIRECTORY_SEPARATOR === '\\') {
            $base = getenv('LOCALAPPDATA') ?: getenv('APPDATA') ?: getenv('TEMP');
        } else {
            $base = getenv('XDG_CACHE_HOME');
            if (!$base) {
                $home = getenv('HOME');
                $base = $home ? $home . DIRECTORY_SEPARATOR . '.cache' : sys_get_temp_dir();
            }
        }

        return rtrim($base, '/\\') . DIRECTORY_SEPARATOR . 'phpunit-dispatcher';
    }

    public static function ensure($directory)
    {
        if (is_dir($directory)) {
            return is_writable($directory);
        }

        return mkdir($directory, 0755, true) || is_dir($directory);
    }

    public static function read($filename)
    {
        return is_file($filename) ? file_get_contents($filename) : false;
    }

    public static function readJson($filename)
    {
        $content = self::read($filename);
        if ($content === false || $content === '') {
            return array();
        }

        $value = json_decode($content, true);
        return is_array($value) ? $value : array();
    }

    public static function writeAtomic($filename, $contents)
    {
        $directory = dirname($filename);
        if (!self::ensure($directory)) {
            return false;
        }

        $temporary = tempnam($directory, '.pud-');
        if ($temporary === false) {
            return false;
        }

        $written = file_put_contents($temporary, $contents, LOCK_EX);
        if ($written === false) {
            @unlink($temporary);
            return false;
        }

        @chmod($temporary, 0644);
        if (!@rename($temporary, $filename)) {
            @unlink($temporary);
            return false;
        }

        return true;
    }

    public static function withLock($filename, $callback)
    {
        $handle = @fopen($filename, 'c+');
        if ($handle === false || !@flock($handle, LOCK_EX)) {
            if (is_resource($handle)) {
                fclose($handle);
            }
            throw new RuntimeException("Unable to lock cache file: $filename");
        }

        try {
            $result = call_user_func($callback);
            flock($handle, LOCK_UN);
            fclose($handle);
            return $result;
        } catch (Exception $exception) {
            flock($handle, LOCK_UN);
            fclose($handle);
            throw $exception;
        }
    }

    public static function remove($directory)
    {
        if (!is_dir($directory)) {
            return true;
        }

        self::clear($directory);
        return @rmdir($directory);
    }

    public static function clear($directory)
    {
        if (!is_dir($directory)) {
            return true;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                @rmdir($item->getPathname());
            } else {
                @unlink($item->getPathname());
            }
        }

        return true;
    }
}
