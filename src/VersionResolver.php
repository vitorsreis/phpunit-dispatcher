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

    public static function fallbackMapping()
    {
        return array(
            '4' => '4.8.36',
            '5' => '5.7.27',
            '6' => '6.5.14',
            '7' => '7.5.20',
            '8' => '8.5.56',
            '9' => '9.6.38',
            '10' => '10.5.66',
            '11' => '11.5.57',
            '12' => '12.5.43',
            '13' => '13.4.6'
        );
    }

    public static function withFallbackMapping($mapping)
    {
        $mapping = is_array($mapping) ? $mapping : array();
        foreach (self::fallbackMapping() as $major => $version) {
            if (!isset($mapping[$major]) || version_compare($mapping[$major], $version, '<')) {
                $mapping[$major] = $version;
            }
        }

        ksort($mapping);
        return $mapping;
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
