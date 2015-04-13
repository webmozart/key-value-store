Changelog
=========

* 1.0.0-beta3 (2015-04-13)

 * replaced `Assert` by webmozart/assert
 
* 1.0.0-beta2 (2015-01-21)

 * added `PhpRedisStore`
 * added `CachedStore`
 * removed optional argument `$cache` from `JsonFileStore::__construct()`
 * removed implementations that don't make sense for a key-value store: 
   * `MemcacheStore` (not persistent)
   * `MemcachedStore` (not persistent)
   * `SharedMemoryStore` (not persistent)
 * added `Serializer` utility
 * `KeyValueStore::get()` now throws an exception if the value cannot be unserialized

* 1.0.0-beta (2015-01-12)

 * first release
