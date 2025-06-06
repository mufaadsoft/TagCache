<?php

namespace MSL;

/**
 * V 0.9.3.5
 * Updated 2022-05-18 By MM
 * Updated 2025-06-02 By MM
 * DO NOT EDIT THIS FILE
 * THIS FILE CONTAIN CORE FUNCTIONALITY OF ADVANCE CACHING SYSTEM
 * AVOID EDITING
 * 2021-07-07 BY MM
 */

use Closure;
use Exception;
use Illuminate\Support\Facades\File;

class TagCache
{

    protected $cache_path = null;
    public function __construct()
    {
        $this->cache_path = storage_path('framework' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'micro');
    }
    public static function remember($key, $time, Closure $callback, $tags = [])
    {
        if ($time == 0 && sizeof($tags) > 0) {
            return $callback();
        }
        $now = time();
        $ttl = ($now + $time);
        if (sizeof($tags) > 0) {
            $cache_path = self::createFolderStructure($tags, $key);
            if (file_exists($cache_path)) {
                $verifyCache = self::technicallyGettingCacheData($cache_path, $now);
                if ($verifyCache === "no-cache-mm-msl") {
                    return self::saveCache($cache_path, $callback, $ttl, $now);
                }
                return $verifyCache;
            } else {
                return self::saveCache($cache_path, $callback, $ttl, $now);
            }
        } else {
            return $callback();
        }
    }


    private static function technicallyGettingCacheData($cache_path, $now)
    {
        $get = File::get($cache_path);
        $technical_data = self::cacheTechnical($get);
        $ttl = $technical_data['ttl'];
        $data = $technical_data['data'];
        if ($ttl > $now) {
            return self::getCache($data);
        } else {
            return "no-cache-mm-msl";
        }
    }

    public static function flush_cache($tags = [], $key = false)
    {
        if ($key) {
            return self::removeCache(self::createFolderStructure($tags, $key));
        } else {
            return self::removeCache(self::createFolderStructureOnly($tags), true);
        }
    }

    public static function flush_all()
    {
        File::deleteDirectory(self::createFolderStructureOnly([]));
        return true;
    }

    private static function releaseFolder($path)
    {
        if (file_exists($path)) {
            return $path;
        } else {
            mkdir($path, 0755, true);
            return $path;
        }
    }

    private static function createFolderStructureOnly($tags)
    {
        $path = app(TagCache::class)->cache_path;
        $folder = '';
        foreach ($tags as $tag) {
            $folder .= DIRECTORY_SEPARATOR . $tag;
        }

        return $path . $folder;
    }
    private static function createFolderStructure($tags, $key)
    {
        $path = self::createFolderStructureOnly($tags);
        return self::releaseFolder($path) . DIRECTORY_SEPARATOR . md5($key) . "-MM";
    }

    private static function neuralyzer($data, $decode = false)
    {
        if ($decode) {
            return unserialize($data);
        } else {
            return serialize($data);
        }
    }

    private static function saveCache($path, $data, $ttl, $now)
    {
        try {
            $neuralyzer = self::neuralyzer($data());
            $store = $ttl . $neuralyzer;
            File::put($path, $store);
            // return $data();
            $return = self::technicallyGettingCacheData($path, $now);
            if (!$return) {
                return $data();
            }
            return $return;
        } catch (Exception $e) {
            return $data();
        }
    }

    private static function getCache($data)
    {
        return self::neuralyzer($data, true);
    }

    private static function removeCache($path, $directory = false)
    {
        if ($directory) {
            File::deleteDirectory($path);
        } else {
            File::delete($path);
        }
        return true;
    }

    private static function cacheTechnical($data)
    {
        try {
            preg_match("/([0-9]+)([a-zA-Z0-9]:)/", $data, $extraction);
            $get_ttl = !empty($extraction[1]) ? $extraction[1] : 0;
            $encoded_data = preg_replace("/([0-9]+)([a-zA-Z0-9]:)/", $extraction[2], $data, 1);
            return ["ttl" => $get_ttl, "data" => $encoded_data];
        } catch (Exception $e) {
            return ["ttl" => 0, "data" => ""];
        }
    }
}
