<?php

namespace PUD;

use Exception;
use Phar;
use RuntimeException;

class Application
{
    public static function run($argv)
    {
        try {
            $arguments = array_slice($argv, 1);
            if (in_array('--pud-version', $arguments, true)) {
                echo VersionResolver::DISPATCHER_VERSION . PHP_EOL;
                return 0;
            }

            if (in_array('--pud-help', $arguments, true)) {
                Application::help();
                return 0;
            }

            Logger::setLevel(in_array('--pud-verbose', $arguments, true) ? 'trace' : 'error');
            $cacheDirectory = Application::option($arguments, '--pud-cache-dir');
            $cacheDirectory = Cache::directory($cacheDirectory);
            if (!Cache::ensure($cacheDirectory)) {
                return Application::fail("Unable to create cache directory: $cacheDirectory");
            }

            if (in_array('--pud-clear-cache', $arguments, true)) {
                Cache::clear($cacheDirectory);
                if (!Cache::ensure($cacheDirectory)) {
                    return Application::fail("Unable to recreate cache directory: $cacheDirectory");
                }
            }

            $phpunitVersion = Application::option($arguments, '--pud-phpunit-version');
            if ($phpunitVersion !== null && !VersionResolver::isValidRelease($phpunitVersion)) {
                return Application::fail("Invalid PHPUnit version: $phpunitVersion");
            }

            if ($phpunitVersion === null) {
                $phpunitVersion = Application::resolveVersion($cacheDirectory, $arguments);
            }

            $pharFile = $cacheDirectory . DIRECTORY_SEPARATOR . "phpunit-$phpunitVersion.phar";
            Cache::withLock($pharFile . '.lock', function () use ($pharFile, $phpunitVersion, $arguments) {
                if (Application::isCachedPhar($pharFile)) {
                    return;
                }

                if (in_array('--pud-offline', $arguments, true)) {
                    throw new RuntimeException("PHPUnit $phpunitVersion is not available in the cache.");
                }
                $response = Http::get("https://phar.phpunit.de/phpunit-$phpunitVersion.phar");
                if ($response['code'] !== 200 || !is_string($response['content']) || $response['content'] === '') {
                    throw new RuntimeException("Unable to download PHPUnit $phpunitVersion: $response[error]");
                }
                if (!Cache::writeAtomic($pharFile, $response['content']) || !Application::isValidPhar($pharFile)) {
                    @unlink($pharFile);
                    throw new RuntimeException('Downloaded PHPUnit PHAR is invalid or could not be cached.');
                }
            });

            Logger::success("Running PHPUnit $phpunitVersion..." . PHP_EOL);
            return Application::execute($pharFile, $arguments);
        } catch (Exception $exception) {
            return Application::fail($exception->getMessage());
        }
    }

    /** @throws Exception */
    private static function resolveVersion($cacheDirectory, $arguments)
    {
        $phpVersion = PHP_MAJOR_VERSION . PHP_MINOR_VERSION;
        $major = VersionResolver::phpunitMajorVersion(PHP_VERSION);
        if ($major === false) {
            throw new Exception('PHP 5.3 or newer is required. Current version: ' . PHP_VERSION);
        }

        $mappingFile = $cacheDirectory . DIRECTORY_SEPARATOR . 'mapping.json';
        $mapping = Cache::readJson($mappingFile);
        $force = in_array('--pud-force-update', $arguments, true);
        if (!$force && isset($mapping[$phpVersion]) && VersionResolver::isValidRelease($mapping[$phpVersion])) {
            return $mapping[$phpVersion];
        }

        $repositoryFile = $cacheDirectory . DIRECTORY_SEPARATOR . 'repository.json';
        $repository = Cache::readJson($repositoryFile);
        if ($force || !isset($repository['mapping'][$major])) {
            if (in_array('--pud-offline', $arguments, true)) {
                throw new Exception('No cached PHPUnit repository is available for offline mode.');
            }
            $response = Http::get('https://phar.phpunit.de', array(
                CURLOPT_HTTPHEADER => array(
                    'If-None-Match: ' . (isset($repository['etag']) ? $repository['etag'] : ''),
                    'If-Modified-Since: ' . (isset($repository['last-modified']) ? $repository['last-modified'] : '')
                )
            ));

            if ($response['code'] === 304 && isset($repository['mapping'])) {
                // Keep the cached repository.
            } elseif ($response['code'] === 200) {
                $repository['mapping'] = VersionResolver::latestByMajor($response['content']);
                if (!$repository['mapping']) {
                    throw new Exception('Unable to parse the PHPUnit repository.');
                }

                if (isset($response['headers']['etag'])) {
                    $repository['etag'] = $response['headers']['etag'];
                }

                if (isset($response['headers']['last-modified'])) {
                    $repository['last-modified'] = $response['headers']['last-modified'];
                }

                Cache::writeAtomic($repositoryFile, json_encode($repository));
            } else {
                throw new Exception("Unable to update PHPUnit repository: HTTP $response[code]");
            }

            $repository['mapping'] = VersionResolver::withFallbackMapping(
                isset($repository['mapping']) ? $repository['mapping'] : array()
            );
        }

        if (!isset($repository['mapping'][$major])) {
            throw new Exception('No PHPUnit release is available for PHP ' . PHP_VERSION . '.');
        }

        $mapping[$phpVersion] = $repository['mapping'][$major];
        Cache::writeAtomic($mappingFile, json_encode($mapping));
        return $mapping[$phpVersion];
    }

    private static function execute($pharFile, $arguments)
    {
        $command = Application::buildCommand($pharFile, $arguments);
        passthru($command, $exitCode);
        return $exitCode;
    }

    public static function buildCommand($pharFile, $arguments, $runtimeArguments = null)
    {
        if ($runtimeArguments === null) {
            $phpRuntimeArguments = new PhpRuntimeArguments();
            $inheritedPhpArguments = $phpRuntimeArguments->arguments();
        } else {
            $inheritedPhpArguments = $runtimeArguments;
        }
        $explicitPhpArguments = array();
        $phpunitArguments = array();
        $count = count($arguments);

        for ($index = 0; $index < $count; $index++) {
            $argument = $arguments[$index];

            if (strpos($argument, '--pud-') === 0) {
                continue;
            }

            // PHP CLI options must precede the PHAR path. In particular,
            // `-dfoo=bar` and `-d foo=bar` configure the child PHP process.
            if ($argument === '-d') {
                if (isset($arguments[$index + 1])) {
                    $explicitPhpArguments[] = $argument . $arguments[++$index];
                }
                continue;
            }

            if (strpos($argument, '-d') === 0 && strlen($argument) > 2) {
                $explicitPhpArguments[] = $argument;
                continue;
            }

            $phpunitArguments[] = $argument;
        }

        $phpArguments = Application::mergePhpArguments($inheritedPhpArguments, $explicitPhpArguments);
        $phpBinary = defined('PHP_BINARY') ? PHP_BINARY : 'php';
        $parts = array(escapeshellarg($phpBinary));
        foreach ($phpArguments as $argument) {
            $parts[] = escapeshellarg($argument);
        }
        $parts[] = escapeshellarg($pharFile);
        foreach ($phpunitArguments as $argument) {
            $parts[] = escapeshellarg($argument);
        }

        return implode(' ', $parts);
    }

    private static function mergePhpArguments($runtimeArguments, $toolArguments)
    {
        $toolKeys = array();
        foreach ($toolArguments as $argument) {
            $toolKeys[Application::phpArgumentKey($argument)] = true;
        }

        $merged = array();
        foreach ($runtimeArguments as $argument) {
            if (!isset($toolKeys[Application::phpArgumentKey($argument)])) {
                $merged[] = $argument;
            }
        }

        foreach ($toolArguments as $argument) {
            $merged[] = $argument;
        }

        return $merged;
    }

    private static function phpArgumentKey($argument)
    {
        if (strpos($argument, '-d') !== 0 || strlen($argument) <= 2) {
            return $argument;
        }

        $definition = substr($argument, 2);
        $separator = strpos($definition, '=');
        $name = $separator === false ? $definition : substr($definition, 0, $separator);
        return '-d' . strtolower($name);
    }

    private static function isCachedPhar($filename)
    {
        if (!is_file($filename) || filesize($filename) < 4) {
            return false;
        }

        $handle = @fopen($filename, 'rb');
        if (!$handle) {
            return false;
        }
        $header = fread($handle, 4);
        fclose($handle);
        return $header !== '404' && Application::isValidPhar($filename);
    }

    private static function isValidPhar($filename)
    {
        try {
            $phar = new Phar($filename);
            unset($phar);
            return true;
        } catch (Exception $exception) {
            return false;
        }
    }

    private static function option($arguments, $name)
    {
        $prefix = "$name=";
        foreach ($arguments as $argument) {
            if (strpos($argument, $prefix) === 0) {
                return substr($argument, strlen($prefix));
            }
        }
        return null;
    }

    private static function fail($message)
    {
        Logger::error($message . PHP_EOL);
        return 1;
    }

    private static function help()
    {
        echo "PHPUnit Dispatcher " . VersionResolver::DISPATCHER_VERSION . PHP_EOL;
        echo "Usage: phpunit-dispatcher [PHPUnit options]" . PHP_EOL;
        echo "  --pud-version                 Show dispatcher version" . PHP_EOL;
        echo "  --pud-phpunit-version=X       Use an explicit PHPUnit release" . PHP_EOL;
        echo "  --pud-cache-dir=PATH          Use a custom cache directory" . PHP_EOL;
        echo "  --pud-force-update            Refresh the PHPUnit repository" . PHP_EOL;
        echo "  --pud-offline                 Never access the network" . PHP_EOL;
        echo "  --pud-clear-cache             Clear the local cache" . PHP_EOL;
        echo "  --pud-verbose                 Show diagnostic output" . PHP_EOL;
    }
}
