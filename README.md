Webmozart Key-Value-Store
=========================

[![Build Status](https://travis-ci.org/webmozart/key-value-store.svg?branch=master)](https://travis-ci.org/webmozart/key-value-store)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/webmozart/key-value-store/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/webmozart/key-value-store/?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/61586798-236a-462a-8429-d8311c1a2500/mini.png)](https://insight.sensiolabs.com/projects/61586798-236a-462a-8429-d8311c1a2500)
[![Latest Stable Version](https://poser.pugx.org/webmozart/key-value-store/v/stable.svg)](https://packagist.org/packages/webmozart/key-value-store)
[![Total Downloads](https://poser.pugx.org/webmozart/key-value-store/downloads.svg)](https://packagist.org/packages/webmozart/key-value-store)
[![Dependency Status](https://www.versioneye.com/php/webmozart:key-value-store/1.0.0/badge.svg)](https://www.versioneye.com/php/webmozart:key-value-store/1.0.0)

Latest release: [1.0.0-beta](https://packagist.org/packages/webmozart/key-value-store#1.0.0-beta)

A key-value store API with implementations for different backends.

All contained key-value stores implement the interface [`KeyValueStore`]. The
following stores are currently supported:

* [`ArrayStore`]
* [`JsonFileStore`]
* [`MemcacheStore`]
* [`MemcachedStore`]
* [`NullStore`]
* [`PredisStore`]
* [`RiakStore`]
* [`SharedMemoryStore`]

FAQ
---

**Why not use [Doctrine Cache]?**

Caching is **not** key-value storage. When you use a cache, you accept that keys
may disappear for various reasons:

* Keys may expire.
* Keys may be overwritten when the cache is full.
* Keys may be lost after shutdowns.
* ...

In another word, caches are *volatile*.

Key-value stores, on the other hand, are *persistent*. When you write a key to a
key-value store today, you expect it to exist tomorrow.

Hence the two libraries fulfill two very different purposes, even if their
interfaces and implementations are often similar.

Authors
-------

* [Bernhard Schussek] a.k.a. [@webmozart]
* [The Community Contributors]

Installation
------------

Use [Composer] to install the package:

```
$ composer require webmozart/key-value-store@dev
```

Contribute
----------

Contributions to the package are always welcome!

* Report any bugs or issues you find on the [issue tracker].
* You can grab the source code at the package's [Git repository].

Support
-------

If you are having problems, send a mail to bschussek@gmail.com or shout out to
[@webmozart] on Twitter.

License
-------

All contents of this package are licensed under the [MIT license].

[Composer]: https://getcomposer.org
[Bernhard Schussek]: http://webmozarts.com
[The Community Contributors]: https://github.com/webmozart/key-value-store/graphs/contributors
[issue tracker]: https://github.com/webmozart/key-value-store/issues
[Git repository]: https://github.com/webmozart/key-value-store
[@webmozart]: https://twitter.com/webmozart
[MIT license]: LICENSE
[Doctrine Cache]: https://github.com/doctrine/cache
[`KeyValueStore`]: src/Api/KeyValueStore.php
[`ArrayStore`]: src/ArrayStore.php
[`JsonFileStore`]: src/JsonFileStore.php
[`MemcacheStore`]: src/MemcacheStore.php
[`MemcachedStore`]: src/MemcachedStore.php
[`NullStore`]: src/NullStore.php
[`PredisStore`]: src/PredisStore.php
[`RiakStore`]: src/RiakStore.php
[`SharedMemoryStore`]: src/SharedMemoryStore.php
