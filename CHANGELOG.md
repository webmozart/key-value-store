Changelog
=========

* 1.0.0-beta6 (2015-10-02)

 * added `SerializingArrayStore`
 * added `DbalStore`

* 1.0.0-beta5 (2015-08-11)

 * added `AbstractDecorator`
 * added `AbstractRedisStore`
 * added `CountableStore`
 * added `SortableStore`
 * renamed `CachedStore` to `CachingDecorator`
 * added `CountableDecorator`
 * added `SortableDecorator`
 * implemented `CountableStore` and `SortableStore` in `ArrayStore`,
  `JsonFileStore` and `NullStore`
 * made `KeyUtil` final
 * made `Serializer` final

* 1.0.0-beta4 (2015-05-28)

 * added `KeyValueStore::keys()`
 * renamed `KeyValueStore::has()` to `exists()`
 * added `KeyValueStore::getOrFail()`
 * added `KeyValueStore::getMultiple()`
 * added `KeyValueStore::getMultipleOrFail()`

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
