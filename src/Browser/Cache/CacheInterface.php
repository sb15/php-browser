<?php

namespace Sb\Browser\Cache;

interface CacheInterface
{
    public function exist($key);
    public function load($key);
    public function save($key, $value);
}