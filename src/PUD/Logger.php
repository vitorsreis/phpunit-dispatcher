<?php

/**
 * This file is part of phpunit-dispatcher
 * @author Vitor Reis <vitor@d5w.com.br>
 */

namespace PUD;

use InvalidArgumentException;

class Logger
{
    private static $level = 'trace';

    private static $levels = array(
        'trace',
        'success',
        'error'
    );

    /**
     * @param string<'trace'|'success'|'error'> $level
     * @return void
     */
    public static function setLevel($level)
    {
        if (!in_array($level, static::$levels)) {
            throw new InvalidArgumentException("Invalid log level: $level");
        }

        static::$level = $level;
    }

    /**
     * @param string<'trace'|'success'|'error'> $level
     * @param string $value
     * @param bool $prefix
     * @return void
     */
    private static function log($level, $value, $prefix = true)
    {
        if (!in_array($level, static::$levels)) {
            throw new InvalidArgumentException("Invalid log level: $level");
        }

        if (array_search($level, static::$levels) < array_search(static::$level, static::$levels)) {
            return;
        }

        $default = "\033[0m"; // Default

        switch ($level) {
            case 'error':
                $color = "\033[31m"; // Red
                break;
            case 'success':
                $color = "\033[32m"; // Green
                break;
            default:
                $color = $default; // Default
                break;
        }

        if ($prefix) {
            $prefix = "[PUD " . date('Y-m-d H:i:s') . "] ";
        }

        echo $default, $prefix, $color, $value, $default;
    }

    /**
     * @param string $value
     * @param bool $prefix
     * @return void
     */
    public static function trace($value, $prefix = true)
    {
        static::log('trace', $value, $prefix);
    }

    /**
     * @param string $value
     * @param bool $prefix
     * @return void
     */
    public static function success($value, $prefix = true)
    {
        static::log('success', $value, $prefix);
    }

    /**
     * @param string $value
     * @param bool $prefix
     * @return void
     */
    public static function error($value, $prefix = true)
    {
        static::log('error', $value, $prefix);
    }
}
