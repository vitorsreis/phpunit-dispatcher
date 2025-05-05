<?php

/**
 * This file is part of phpunit-dispatcher
 * @author Vitor Reis <vitor@d5w.com.br>
 */

require_once __DIR__ . '/PUD/File.php';
require_once __DIR__ . '/PUD/Http.php';
require_once __DIR__ . '/PUD/Logger.php';

use PUD\File;
use PUD\Http;
use PUD\Logger;

try {
    $version = trim(@file_get_contents(__DIR__ . '/version.txt') ?: '');
    echo "\033[0mPHPUnit Dispatcher $version by Vitor Reis.\n";

    Logger::setLevel(in_array('--pud-verbose', $argv) ? 'trace' : 'error');

    $phpVersion = PHP_MAJOR_VERSION . PHP_MINOR_VERSION;

    Logger::trace("Initializing dispatcher to PHP$phpVersion\n");

    $phpunitOrigin = 'https://phar.phpunit.de';

    $phpunitMajorVersions = array(
        "53" => "4",
        "54" => "4",
        "55" => "4",
        "56" => "5",
        "70" => "6",
        "71" => "7",
        "72" => "8",
        "73" => "9",
        "74" => "9",
        "81" => "10",
        "82" => "11",
        "83" => "12",
        "84" => "12"
    );

    Logger::trace("Checking PHP version: ");

    if (version_compare(PHP_VERSION, '5.3', '<')) {
        Logger::error("Failed, PHP53+ is required. Current version: " . PHP_VERSION . "\n", false);
        exit(1);
    }

    Logger::success("Success\n", false);

    Logger::trace("Checking data directory: ");

    $dataDir = dirname(substr(__DIR__, 7)) . DIRECTORY_SEPARATOR . 'phpunit-dispatcher';
    if (!is_dir($dataDir) && !@mkdir($dataDir, 0644, true)) {
        Logger::error("Failed to create data directory: $dataDir\n", false);
        exit(1);
    }

    Logger::success("Success, " . realpath($dataDir) . "\n", false);

    foreach ($argv as $arg) {
        if (preg_match('/^--pud-phpunit-version=(.+)$/', $arg, $matches)) {
            $phpunitVersion = $matches[1];
            break;
        }
    }

    if (isset($phpunitVersion)) {
        Logger::trace("Select PHPUnit version: ");
        Logger::success("Success, $phpunitVersion (user defined)\n", false);
    } else {
        Logger::trace("Getting mapping: ");

        $mappingFP = new File($mappingFile = "$dataDir/mapping.json");
        if ($mappingFP->open() === false) {
            Logger::error("Failed to open mapping file: $mappingFile\n", false);
            exit(1);
        }

        $mappingFP->lock();

        $mapping = json_decode($mappingFP->read() ?: '', true) ?: [];

        Logger::success("Success\n", false);

        if (!$mapping || !isset($mapping[$phpVersion]) || in_array("--pud-force-update", $argv)) {
            Logger::trace("Getting PHPUnit major version: ");

            if (!isset($phpunitMajorVersions[$phpVersion])) {
                Logger::error("Failed, missing PHPUnit version mapping for PHP$phpVersion\n", false);
                exit(1);
            }

            $phpunitMajorVersion = $phpunitMajorVersions[$phpVersion];

            Logger::success("Success, $phpunitMajorVersion\n", false);

            Logger::trace("Getting repository: ");

            $repositoryFP = new File($repositoryFile = "$dataDir/repository.json");
            if ($repositoryFP->open() === false) {
                Logger::error("Failed to open repository file: $repositoryFile\n", false);
                exit(1);
            }

            $repositoryFP->lock();

            $repository = json_decode($repositoryFP->read() ?: '', true) ?: [];

            Logger::success("Success\n", false);

            if (!isset($repository['mapping'][$phpunitMajorVersion]) || in_array("--pud-force-update", $argv)) {
                Logger::trace("Updating repository: ");

                $response = Http::get($phpunitOrigin, array(
                    CURLOPT_HTTPHEADER => array(
                        'If-None-Match: ' . (isset($repository['etag']) ? $repository['etag'] : ''),
                        'If-Modified-Since: ' . (isset($repository['last-modified']) ? $repository['last-modified'] : ''),
                    ),
                    CURLOPT_HEADERFUNCTION => static function ($curl, $header) use (&$repository) {
                        if (preg_match('/^ETag:\s*(.+)$/mi', $header, $matches)) {
                            $repository['etag'] = trim($matches[1]);
                        } elseif (preg_match('/^Last-Modified:\s*(.+)$/mi', $header, $matches)) {
                            $repository['last-modified'] = trim($matches[1]);
                        }
                        return strlen($header);
                    }
                ));

                if ($response['code'] === 304 && !empty($repository['mapping'])) {
                    Logger::success("Success, cached data\n", false);
                } else if ($response['code'] !== 200) {
                    Logger::error("Failed, HTTP $response[code]" . ($response['error'] ? ", $response[error]" : '') . "\n", false);
                    exit(1);
                } else {
                    if ($response['content'] === false) {
                        Logger::error("Failed, empty content" . ($response['error'] ? ", $response[error]" : '') . "\n", false);
                        exit(1);
                    }

                    if (!preg_match_all('/href=".+\/phpunit-(\d+\.\d+\.\d+)\.phar"/', $response['content'], $matches)) {
                        Logger::error("Failed to parse PHPUnit repository\n", false);
                        exit(1);
                    }

                    $repository['mapping'] = array();

                    foreach ($matches[1] as $phpunitVersion) {
                        $matchPhpunitMajorVersion = substr($phpunitVersion, 0, strpos($phpunitVersion, '.'));
                        if (
                            !isset($repository['mapping'][$matchPhpunitMajorVersion])
                            || version_compare($repository['mapping'][$matchPhpunitMajorVersion], $phpunitVersion, '<')
                        ) $repository['mapping'][$matchPhpunitMajorVersion] = $phpunitVersion;
                    }

                    if (empty($repository['mapping'])) {
                        Logger::error("Failed to parse PHPUnit repository\n", false);
                        exit(1);
                    }

                    ksort($repository['mapping']);

                    $repositoryFP->write(json_encode($repository, 448));

                    Logger::success("Success, repository updated\n", false);
                }
            }

            unset($repositoryFP);

            Logger::trace("Select PHPUnit version: ");

            if (!isset($repository['mapping'][$phpunitMajorVersion])) {
                Logger::error("Failed, missing PHPUnit version with major version $phpunitMajorVersion\n", false);
                exit(1);
            }

            $phpunitVersion = $repository['mapping'][$phpunitMajorVersion];

            Logger::success("Success, $phpunitMajorVersion -> $phpunitVersion\n", false);

            $mapping[$phpVersion] = $phpunitVersion;
            ksort($mapping);

            $mappingFP->write(json_encode($mapping, 448));
        } else {
            Logger::trace("Select PHPUnit version: ");

            $phpunitVersion = $mapping[$phpVersion];

            Logger::success("Success, $phpunitVersion\n", false);
        }
    }

    unset($mappingFP);

    Logger::trace('Checking PHPUnit phar: ');

    $phpunitPharFP = new File($phpunitPharFile = "$dataDir/phpunit-$phpunitVersion.phar");
    if ($phpunitPharFP->open() === false) {
        Logger::error("Failed to open PHPUnit phar file: $phpunitPharFile\n", false);
        exit(1);
    }

    $phpunitPharFP->lock();

    if (!$phpunitPharFP->size()) {
        Logger::trace("Downloading PHPUnit phar file: ", false);

        $response = Http::get("$phpunitOrigin/phpunit-$phpunitVersion.phar");

        if ($response['code'] !== 200) {
            if ($response['code'] === 404) {
                Logger::error("Failed, PHPUnit version $phpunitVersion not found\n", false);
                $phpunitPharFP->write('404');
                unset($phpunitPharFP);
            } else {
                Logger::error("Failed, HTTP $response[code]" . ($response['error'] ? ", $response[error]" : '') . "\n", false);
            }

            exit(1);
        }

        if (empty($response['content'])) {
            Logger::error("Failed, empty content" . ($response['error'] ? ", $response[error]" : '') . "\n", false);
            exit(1);
        }

        $phpunitPharFP->write($response['content']);

        Logger::success("Success\n", false);
    } elseif ($phpunitPharFP->read(0, 3) === '404') {
        Logger::error("Failed, PHPUnit version $phpunitVersion not found\n", false);
        exit(1);
    } else {
        Logger::success("Success, already downloaded\n", false);
    }

    unset($phpunitPharFP);

    Logger::success("Running PHPUnit $phpunitVersion...\n");

    $command = escapeshellcmd(PHP_BINARY);
    $command .= " " . escapeshellcmd($phpunitPharFile);
    $command .= " " . implode(' ', array_map('escapeshellarg', array_filter(
            array_slice($argv, 1),
            static function ($arg) {
                return strpos($arg, '--pud-') !== 0;
            }
        )));

    passthru($command, $exitCode);

    exit($exitCode);
} catch (Exception $e) {
    Logger::error("Failed, {$e->getMessage()}\n", false);
    exit(1);
} catch (Throwable $e) {
    Logger::error("Failed, {$e->getMessage()}\n", false);
    exit(1);
}
