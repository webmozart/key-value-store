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

use Webmozart\KeyValueStore\Api\KeyValueStore;
use Webmozart\KeyValueStore\Api\SortableStore;

/**
 * A sortable decorator implementing a sort system for any store.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
class SortableDecorator implements SortableStore
{
    /**
     * @var KeyValueStore
     */
    private $store;

    /**
     * @var int
     */
    private $flags;

    /**
     * Creates the store.
     *
     * @param KeyValueStore $store The store to sort.
     */
    public function __construct(KeyValueStore $store)
    {
        $this->store = $store;
    }

    /**
     * {@inheritdoc}
     */
    public function sort($flags = SORT_REGULAR)
    {
        $this->flags = $flags;
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        $this->flags = null;
        $this->store->set($key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        return $this->store->get($key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrFail($key)
    {
        return $this->store->getOrFail($key);
    }

    /**
     * {@inheritdoc}
     */
    public function getMultiple(array $keys, $default = null)
    {
        return $this->store->getMultiple($keys, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function getMultipleOrFail(array $keys)
    {
        return $this->store->getMultipleOrFail($keys);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        $this->store->remove($key);
    }

    /**
     * {@inheritdoc}
     */
    public function exists($key)
    {
        return $this->store->exists($key);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->store->clear();
    }

    /**
     * {@inheritdoc}
     */
    public function keys()
    {
        $keys = $this->store->keys();

        if (null !== $this->flags) {
            sort($keys, $this->flags);
        }

        return $keys;
    }
}
