# Laravel Tag Caching
## Version 0.9.4


This library is build to fulfill the requirement of tag caching system

## Features
- Stores caching data in a organize way
- Auto cache expiring
- Reducing the SQL query requests

### Installation
```
composer require msolutions/tag-cache
```

### Implementing

```
use MSL\TagCache;

//caching query or any other data

$unique_key = "unique-name";
$ttl = (60*60);//timing for expiry in seconds

$result = TagCache::remember($unique_key, $ttl, function() {
    //database query fetching should be inside this function
    $cache_data = Model::get(); //database fetch query 
    return $cache_data;
}, ["tag1", "tag2"]);

```

### Removing Cache

```
$unique_key = "unique-name"; //you cache unique key
TagCache::flush_cache(["tag1", "tag2"], $unique_key);
```

### Removing all cache

```
TagCache::flush_all();
```

This open source package is developed for general use, any of developers can use this for free.
- Please share your comments and ideas to improve the package.
