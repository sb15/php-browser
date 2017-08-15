<?php

namespace Sb\Browser\Cache;

class FileCache implements CacheInterface
{
    private $cacheDir;

    public function __construct($cacheDir)
    {
        $this->cacheDir = $cacheDir;
    }

    public function load($key)
    {
        $file = $this->cacheDir . '/' . sha1($key);
        return file_get_contents($file);
    }

    public function exist($key)
    {
        $file = $this->cacheDir . '/' . sha1($key);
        if (is_file($file)) {
            return file_get_contents($file);
        }
        return null;
    }

    public function save($key, $value)
    {
        $file = $this->cacheDir . '/' . sha1($key);
        file_put_contents($file, $value);
    }
}