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
use Webmozart\KeyValueStore\Assert\Assert;

/**
 * A key-value store backed by a PHP array.
 *
 * The contents of the store are lost when the store is released from memory.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ArrayStore implements KeyValueStore
{
    /**
     * @var array
     */
    private $array;

    /**
     * Creates a new store.
     *
     * @param array $array The values to set initially in the store.
     */
    public function __construct(array $array = array())
    {
        $this->array = $array;
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        Assert::key($key);

        $this->array[$key] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        Assert::key($key);

        return isset($this->array[$key]) ? $this->array[$key] : $default;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        Assert::key($key);

        $removed = isset($this->array[$key]);

        unset($this->array[$key]);

        return $removed;
    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        Assert::key($key);

        return isset($this->array[$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->array = array();
    }

    /**
     * Returns the contents of the store as array.
     *
     * @return array The keys and values in the store.
     */
    public function toArray()
    {
        return $this->array;
    }
}
