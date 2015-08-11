<?php

/*
 * This file is part of the webmozart/key-value-store package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\KeyValueStore\Decorator;

use Webmozart\KeyValueStore\Api\KeyValueStore;

/**
 * A delegating decorator delegate each call of a KeyValueStore method
 * to the internal store.
 *
 * It is used by decorators that need to override only a few specific
 * methods (such as SortableDecorator or CountableDecorator).
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
class AbstractDecorator implements KeyValueStore
{
    /**
     * @var KeyValueStore
     */
    protected $store;

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
    public function set($key, $value)
    {
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
        return $this->store->keys();
    }
}
