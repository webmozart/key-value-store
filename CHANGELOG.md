Changelog
=========

* 1.0.0-beta8 (@release_date@)

 * added `MongoDbStore`

* 1.0.0-beta7 (2016-01-14)

 * added `JsonFileStore::NO_SERIALIZE_STRINGS` and `JsonFileStore::NO_SERIALIZE_ARRAYS`
 * disabled serialization of `null` values in `JsonFileStore`
 * removed code from `JsonFileStore` and relied on webmozart/json instead
 * added `JsonFileStore::ESCAPE_GT_LT`
 * added `JsonFileStore::ESCAPE_AMPERSAND`
 * added `JsonFileStore::ESCAPE_SINGLE_QUOTE`
 * added `JsonFileStore::ESCAPE_DOUBLE_QUOTE`
 * added `JsonFileStore::NO_ESCAPE_SLASH`
 * added `JsonFileStore::NO_ESCAPE_UNICODE`
 * added `JsonFileStore::PRETTY_PRINT`
 * added `JsonFileStore::TERMINATE_WITH_LINE_FEED`

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
