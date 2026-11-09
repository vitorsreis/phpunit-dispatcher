<?php

namespace PUD;

class VersionResolver
{
    const DISPATCHER_VERSION = '2.0.0';

    public static function phpunitMajorVersion($phpVersion)
    {
        $id = PHP_VERSION_ID;
        if (preg_match('/^(\d+)\.(\d+)/', $phpVersion, $matches)) {
            $id = ((int)$matches[1] * 10000) + ((int)$matches[2] * 100);
        }

        if ($id < 50300) {
            return false;
        }
        if ($id < 50600) {
            return '4';
        }
        if ($id < 70000) {
            return '5';
        }
        if ($id < 70100) {
            return '6';
        }
        if ($id < 70200) {
            return '7';
        }
        if ($id < 70300) {
            return '8';
        }
        if ($id < 80100) {
            return '9';
        }
        if ($id < 80200) {
            return '10';
        }
        if ($id < 80300) {
            return '11';
        }
        if ($id < 80400) {
            return '12';
        }

        return '13';
    }

    public static function isValidRelease($version)
    {
        return is_string($version) && !!preg_match('/^\d+\.\d+\.\d+$/', $version);
    }

    public static function latestByMajor($html)
    {
        $mapping = array();
        if (!is_string($html)) {
            return $mapping;
        }

        if (!preg_match_all('/phpunit-(\d+\.\d+\.\d+)\.phar/', $html, $matches)) {
            return $mapping;
        }

        foreach ($matches[1] as $version) {
            $major = substr($version, 0, strpos($version, '.'));
            if (!isset($mapping[$major]) || version_compare($mapping[$major], $version, '<')) {
                $mapping[$major] = $version;
            }
        }

        ksort($mapping);
        return $mapping;
    }
}
