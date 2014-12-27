Webmozart Key-Value-Store
=========================

A key-value store with support for different backends.

Latest release: none

All contained key-value stores implement the interface [`KeyValueStore`]. The
following stores are currently supported:

* [`ArrayStore`]
* [`FlintstoneStore`]
* [`MemcacheStore`]
* [`MemcachedStore`]
* [`NullStore`]
* [`RedisStore`]
* [`RiakStore`]
* [`SharedMemoryStore`]

Authors
-------

* [Bernhard Schussek] a.k.a. [@webmozart]
* [The Community Contributors]

Installation
------------

Use [Composer] to install the package:

```
composer require webmozart/key-value-store@dev
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
[`KeyValueStore`]: src/KeyValueStore.php
[`ArrayStore`]: src/Impl/ArrayStore.php
[`FlintstoneStore`]: src/Impl/FlintstoneStore.php
[`MemcacheStore`]: src/Impl/MemcacheStore.php
[`MemcachedStore`]: src/Impl/MemcachedStore.php
[`NullStore`]: src/Impl/NullStore.php
[`RedisStore`]: src/Impl/RedisStore.php
[`RiakStore`]: src/Impl/RiakStore.php
[`SharedMemoryStore`]: src/Impl/SharedMemoryStore.php
