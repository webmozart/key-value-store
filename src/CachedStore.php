<?php

/*
 * This file is part of the webmozart/key-value-store package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\KeyValueStore;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\ClearableCache;
use Doctrine\Common\Cache\FlushableCache;
use InvalidArgumentException;
use Webmozart\KeyValueStore\Api\KeyValueStore;
use Webmozart\KeyValueStore\Api\NoSuchKeyException;

/**
 * A key-value store replicated in a cache.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class CachedStore implements KeyValueStore
{
    /**
     * @var KeyValueStore
     */
    private $store;

    /**
     * @var Cache|ClearableCache|FlushableCache
     */
    private $cache;

    private $ttl;

    /**
     * Creates the store.
     *
     * @param KeyValueStore $store The cached store.
     * @param Cache         $cache The cache.
     * @param int           $ttl   The time-to-live for cache entries. If set to
     *                             0, cache entries never expire.
     */
    public function __construct(KeyValueStore $store, Cache $cache, $ttl = 0)
    {
        if (!$cache instanceof ClearableCache && !$cache instanceof FlushableCache) {
            throw new InvalidArgumentException(sprintf(
                'The cache must either implement ClearableCache or '.
                'FlushableCache. Got: %s',
                get_class($cache)
            ));
        }

        $this->store = $store;
        $this->cache = $cache;
        $this->ttl = $ttl;
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        $this->store->set($key, $value);
        $this->cache->save($key, $value, $this->ttl);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrFail($key)
    {
        if ($this->cache->contains($key)) {
            return $this->cache->fetch($key);
        }

        $value = $this->store->getOrFail($key);

        $this->cache->save($key, $value, $this->ttl);

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getIfExists($key, $default = null)
    {
        if ($this->cache->contains($key)) {
            return $this->cache->fetch($key);
        }

        try {
            $value = $this->store->getOrFail($key);
        } catch (NoSuchKeyException $e) {
            return $default;
        }

        $this->cache->save($key, $value, $this->ttl);

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getMultipleOrFail(array $keys)
    {
        $values = array();

        // Read cached values from the cache
        foreach ($keys as $i => $key) {
            if ($this->cache->contains($key)) {
                $values[$key] = $this->cache->fetch($key);
                unset($keys[$i]);
            }
        }

        $values = array_replace($values, $this->store->getMultipleOrFail($keys));

        // Write newly fetched values to the cache
        foreach ($keys as $key) {
            $this->cache->save($key, $values[$key], $this->ttl);
        }

        return $values;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        $this->store->remove($key);
        $this->cache->delete($key);
    }

    /**
     * {@inheritdoc}
     */
    public function exists($key)
    {
        if ($this->cache->contains($key)) {
            return true;
        }

        return $this->store->exists($key);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->store->clear();

        if ($this->cache instanceof ClearableCache) {
            $this->cache->deleteAll();
        } else {
            $this->cache->flushAll();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function keys()
    {
        return $this->store->keys();
    }
}
