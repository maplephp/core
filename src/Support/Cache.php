<?php

namespace MaplePHP\Core\Support;


use MaplePHP\Core\App;
use MaplePHP\Cache\Cache as MapleCache;
use Psr\Cache\CacheItemPoolInterface;

class Cache
{

    const CACHE_DIR = "/storage/cache/framework";

    /**
     * Create framework cache directory if missing
     * @param string $addDir
     * @return bool
     */
    private function makeCacheDir(string $addDir = ""): bool
    {
        $dir = App::get()->dir()->cache() . $addDir;
        if (!is_dir($dir)) {
            return mkdir($dir . self::CACHE_DIR, 0777, true);
        }
        return true;
    }

    public function framework(): self
    {
        $inst = clone $this;
        if (!$inst->makeCacheDir("/framework")) {
            throw new \Exception("Cache directory could be not created");
        }
        return $inst;
    }

    public function cache(CacheItemPoolInterface $handler): MapleCache
    {
        return new MapleCache($handler);
    }
}