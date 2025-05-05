<?php

/**
 * This file is part of phpunit-dispatcher
 * @author Vitor Reis <vitor@d5w.com.br>
 */

namespace PUD;

class File
{
    /**
     * @var string
     */
    private $filename;

    /**
     * @var resource|false
     */
    private $handle = false;

    /**
     * @var string
     */
    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    public function __destruct()
    {
        if (!$this->handle) {
            return;
        }

        $this->unlock();
        $this->close();
    }

    /**
     * @return resource|false
     */
    public function handle()
    {
        return $this->handle;
    }

    /**
     * @return resource|false
     */
    public function open($mode = "a+")
    {
        return $this->handle = fopen($this->filename, $mode);
    }

    /**
     * @return bool
     */
    public function close()
    {
        if (!$this->handle) {
            return true;
        }

        $result = !!@fclose($this->handle);
        $this->handle = false;

        return $result;
    }

    /**
     * @param int|null $length If null, read the whole file
     * @return string|false
     */
    public function read($start = 0, $length = null)
    {
        if (!$this->handle) {
            return false;
        }

        fseek($this->handle, $start);

        null === $length && $length = $this->size() - $start;

        0 >= $length && $length = 1;

        return fread($this->handle, $length);
    }

    /**
     * @param string $data
     * @param bool $append
     * @return int|false
     */
    public function write($data, $append = false)
    {
        if (!$this->handle) {
            return false;
        }

        if ($append) {
            fseek($this->handle, 0, SEEK_END);
        } else {
            fseek($this->handle, 0);
            ftruncate($this->handle, 0);
        }

        return fwrite($this->handle, $data);
    }

    /**
     * @return int|false
     */
    public function size()
    {
        if (!$this->handle) {
            return false;
        }

        return filesize($this->filename);
    }

    /**
     * @param bool $exclusive
     * @return bool
     */
    public function lock($exclusive = true)
    {
        if (!$this->handle) {
            return false;
        }

        return @flock($this->handle, $exclusive ? LOCK_EX : LOCK_SH);
    }

    /**
     * @return bool
     */
    public function unlock()
    {
        if (!$this->handle) {
            return false;
        }

        return !!@flock($this->handle, LOCK_UN);
    }
}