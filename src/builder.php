<?php

/**
 * This file is part of phpunit-dispatcher
 * @author Vitor Reis <vitor@d5w.com.br>
 */

require_once __DIR__ . '/PUD/File.php';
require_once __DIR__ . '/PUD/Logger.php';

use PUD\File;
use PUD\Logger;

try {
    Logger::setLevel('trace');

    Logger::trace("Initializing builder...\n");

    Logger::trace("Checking PHAR extension: ");

    if (!class_exists('\\Phar')) {
        Logger::error("Failed, PHAR extension is required\n", false);
        exit(1);
    }

    Logger::success("Success\n", false);

    Logger::trace("Loading composer.json file: ");

    $composerFP = new File($composerFile = __DIR__ . '/../composer.json');
    if ($composerFP->open('r') === false) {
        Logger::error("Failed, missing composer.json file\n", false);
        exit(1);
    }

    $composer = json_decode($composerFP->read() ?: '', true);
    if (!$composer) {
        Logger::error("Failed, invalid composer.json file\n", false);
        exit(1);
    }

    unset($composerFP);

    Logger::success("Success\n", false);

    Logger::trace("Creating dist directory: ");

    $outputDir = __DIR__ . '/../dist';
    if (!is_dir($outputDir) && !@mkdir($outputDir, 0644, true)) {
        Logger::error("Failed to create dist directory: $outputDir\n", false);
        exit(1);
    }

    Logger::success("Success\n", false);

    Logger::trace("Building phar file: ");

    $pharFile = "$outputDir/phpunit-dispatcher.phar";

    is_file($pharFile) && unlink($pharFile);

    $phar = new Phar($pharFile);

    $phar->startBuffering();

    $phar->addFile(__DIR__ . '/PUD/File.php', 'PUD/File.php');

    $phar->addFile(__DIR__ . '/PUD/Http.php', 'PUD/Http.php');

    $phar->addFile(__DIR__ . '/PUD/Logger.php', 'PUD/Logger.php');

    $phar->addFile(__DIR__ . '/dispatcher.php', 'dispatcher.php');

    $phar->addFromString("version.txt", $composer['version']);

    $phar->setStub($phar->createDefaultStub('dispatcher.php'));

    $phar->stopBuffering();

    unset($phar);

    Logger::success("Success\n", false);

    Logger::success("Build completed successfully $composer[version]");
    Logger::trace(", " . realpath($pharFile) . "\n", false);
} catch (Exception $e) {
    Logger::error("Failed, {$e->getMessage()}\n", false);
    exit(1);
} catch (Throwable $e) {
    Logger::error("Failed, {$e->getMessage()}\n", false);
    exit(1);
}
