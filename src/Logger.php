<?php

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
     * @param string $level
     * @return void
     */
    public static function setLevel($level)
    {
        if (!in_array($level, self::$levels)) {
            throw new InvalidArgumentException("Invalid log level: $level");
        }

        self::$level = $level;
    }

    /**
     * @param string $level
     * @param string $value
     * @param bool $prefix
     * @return void
     */
    private static function log($level, $value, $prefix = true)
    {
        if (!in_array($level, self::$levels)) {
            throw new InvalidArgumentException("Invalid log level: $level");
        }

        if (array_search($level, self::$levels) < array_search(self::$level, self::$levels)) {
            return;
        }

        $default = "\033[0m";
        $colored = getenv('NO_COLOR') === false
            && function_exists('posix_isatty')
            && defined('STDOUT')
            && @posix_isatty(STDOUT);

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

        if (!$colored) {
            $color = '';
            $default = '';
        }

        if ($prefix) {
            $prefix = "[PUD " . date('Y-m-d H:i:s') . "] ";
        }

        $stream = $level === 'error' && defined('STDERR') ? STDERR : STDOUT;
        fwrite($stream, $default . $prefix . $color . $value . $default);
    }

    /**
     * @param string $value
     * @param bool $prefix
     * @return void
     */
    public static function trace($value, $prefix = true)
    {
        self::log('trace', $value, $prefix);
    }

    /**
     * @param string $value
     * @param bool $prefix
     * @return void
     */
    public static function success($value, $prefix = true)
    {
        self::log('success', $value, $prefix);
    }

    /**
     * @param string $value
     * @param bool $prefix
     * @return void
     */
    public static function error($value, $prefix = true)
    {
        self::log('error', $value, $prefix);
    }
}
