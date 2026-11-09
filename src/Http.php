<?php

namespace PUD;

/**
 * @template Response of array{
 *     code: int,
 *     content: string|false,
 *     error: string|null,
 *     info: array<string, mixed>,
 *     headers: array<string, string>
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
        $headers = array();

        if ($ch === false) {
            return array('code' => 0, 'content' => false, 'error' => 'Unable to initialize cURL', 'info' => array(), 'headers' => array());
        }

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, 'phpunit-dispatcher/' . VersionResolver::DISPATCHER_VERSION);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($handle, $header) use (&$headers) {
            if (strpos($header, ':') !== false) {
                list($name, $value) = explode(':', $header, 2);
                $headers[strtolower(trim($name))] = trim($value);
            }
            return strlen($header);
        });

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

        if (PHP_VERSION_ID < 80500) {
            curl_close($ch);
        }

        return array(
            'code' => $info['http_code'],
            'content' => $content,
            'error' => ($errno || $error) ? "#$errno $error" : '',
            'info' => $info,
            'headers' => $headers
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
