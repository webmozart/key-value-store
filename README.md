Webmozart Key-Value-Store
=========================

[![Build Status](https://travis-ci.org/webmozart/key-value-store.svg?branch=master)](https://travis-ci.org/webmozart/key-value-store)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/webmozart/key-value-store/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/webmozart/key-value-store/?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/61586798-236a-462a-8429-d8311c1a2500/mini.png)](https://insight.sensiolabs.com/projects/61586798-236a-462a-8429-d8311c1a2500)
[![Latest Stable Version](https://poser.pugx.org/webmozart/key-value-store/v/stable.svg)](https://packagist.org/packages/webmozart/key-value-store)
[![Total Downloads](https://poser.pugx.org/webmozart/key-value-store/downloads.svg)](https://packagist.org/packages/webmozart/key-value-store)
[![Dependency Status](https://www.versioneye.com/php/webmozart:key-value-store/1.0.0/badge.svg)](https://www.versioneye.com/php/webmozart:key-value-store/1.0.0)

Latest release: [1.0.0-beta4](https://packagist.org/packages/webmozart/key-value-store#1.0.0-beta4)

A key-value store API with implementations for different backends.

[API Documentation]

All contained key-value stores implement the interface [`KeyValueStore`]. The
following stores are currently supported:

* [`ArrayStore`]
* [`CachedStore`]
* [`JsonFileStore`]
* [`NullStore`]
* [`PhpRedisStore`]
* [`PredisStore`]
* [`RiakStore`]

FAQ
---

**Why not use [Doctrine Cache]?**

Caching is **not** key-value storage. When you use a cache, you accept that keys
may disappear for various reasons:

* Keys may expire.
* Keys may be overwritten when the cache is full.
* Keys may be lost after shutdowns.
* ...

In another word, caches are *volatile*. This is not a problem, since the cached
data is usually stored safely somewhere else. The point of a cache is to provide
high-performance access to frequently needed data.

Key-value stores, on the other hand, are *persistent*. When you write a key to a
key-value store, you expect it to be there until you delete it. It would be a
disaster if data would silently disappear from a key-value store (or any other
kind of database).

Hence the two libraries fulfill two very different purposes, even if their
interfaces and implementations are often similar.

The [`CachedStore`] actually uses a Doctrine Cache object to cache the data of
a persistent [`KeyValueStore`].

Authors
-------

* [Bernhard Schussek] a.k.a. [@webmozart]
* [The Community Contributors]

Installation
------------

Use [Composer] to install the package:

```
$ composer require webmozart/key-value-store@beta
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
[API Documentation]: https://webmozart.github.io/key-value-store/api
[`KeyValueStore`]: https://webmozart.github.io/key-value-store/api/latest/class-Webmozart.KeyValueStore.Api.KeyValueStore.html
[`ArrayStore`]: https://webmozart.github.io/key-value-store/api/latest/class-Webmozart.KeyValueStore.ArrayStore.html
[`CachedStore`]: https://webmozart.github.io/key-value-store/api/latest/class-Webmozart.KeyValueStore.CachedStore.html
[`JsonFileStore`]: https://webmozart.github.io/key-value-store/api/latest/class-Webmozart.KeyValueStore.JsonFileStore.html
[`NullStore`]: https://webmozart.github.io/key-value-store/api/latest/class-Webmozart.KeyValueStore.NullStore.html
[`PhpRedisStore`]: https://webmozart.github.io/key-value-store/api/latest/class-Webmozart.KeyValueStore.PhpRedisStore.html
[`PredisStore`]: https://webmozart.github.io/key-value-store/api/latest/class-Webmozart.KeyValueStore.PredisStore.html
[`RiakStore`]: https://webmozart.github.io/key-value-store/api/latest/class-Webmozart.KeyValueStore.RiakStore.html
