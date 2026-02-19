<?php

/**
 * This file is part of phpunit-dispatcher
 * @author Vitor Reis <vitor@d5w.com.br>
 */

namespace PUD;

/**
 * @template Response of array{
 *     code: int,
 *     content: string|false,
 *     error: string|null,
 *     info: array<string, mixed>
 * }
 */
class Http
{
    /**
     * @param string $method
     * @param string $url
     * @param mixed $data
     * @param array<int, mixed> $options
     * @return Response
     */
    public static function raw($method, $url, $data = null, $options = array())
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        foreach ($options as $key => $value) {
            curl_setopt($ch, $key, $value);
        }

        $content = curl_exec($ch);
        $info = curl_getinfo($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);

        if (version_compare(PHP_VERSION, '8.5', "<")) {
            curl_close($ch);
        }

        return array(
            'code' => $info['http_code'],
            'content' => $content,
            'error' => ($errno || $error) ? "#$errno $error" : 0,
            'info' => $info
        );
    }

    /**
     * @param string $url
     * @param array<int, mixed> $options
     * @return Response
     */
    public static function get($url, array $options = array())
    {
        return static::raw('GET', $url, null, $options);
    }
}