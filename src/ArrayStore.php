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
use Webmozart\KeyValueStore\Api\NoSuchKeyException;
use Webmozart\KeyValueStore\Assert\Assert;
use Webmozart\KeyValueStore\Util\KeyUtil;

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
        KeyUtil::validate($key);

        $this->array[$key] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        KeyUtil::validate($key);

        if (!array_key_exists($key, $this->array)) {
            throw NoSuchKeyException::forKey($key);
        }

        return $this->array[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        KeyUtil::validate($key);

        $removed = array_key_exists($key, $this->array);

        unset($this->array[$key]);

        return $removed;
    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        KeyUtil::validate($key);

        return array_key_exists($key, $this->array);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->array = array();
    }

    /**
     * {@inheritdoc}
     */
    public function keys()
    {
        return array_keys($this->array);
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
