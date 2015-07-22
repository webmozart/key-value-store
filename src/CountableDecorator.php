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

use Webmozart\KeyValueStore\Api\CountableStore;

/**
 * A countable decorator implementing a count system for any store.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
class CountableDecorator extends AbstractDecorator implements CountableStore
{
    /**
     * In-memory cache invalidated on store modification.
     *
     * @var int
     */
    private $cache;

    /**
     * Is the cache fresh enough to be served?
     *
     * @var bool
     */
    private $cacheIsFresh = false;

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        $this->cacheIsFresh = false;
        $this->store->set($key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        $this->cacheIsFresh = false;
        $this->store->remove($key);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->cacheIsFresh = false;
        $this->store->clear();
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        if (!$this->cacheIsFresh) {
            $this->cache = count($this->store->keys());
            $this->cacheIsFresh = true;
        }

        return $this->cache;
    }
}
