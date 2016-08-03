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
use Webmozart\KeyValueStore\Api\NoSuchKeyException;
use Webmozart\KeyValueStore\Api\SortableStore;
use Webmozart\KeyValueStore\Util\KeyUtil;

/**
 * A key-value store backed by a PHP array.
 *
 * The contents of the store are lost when the store is released from memory.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ArrayStore implements SortableStore, CountableStore
{
    /**
     * Flag: Enable serialization.
     */
    const SERIALIZE = 1;

    /**
     * @var array
     */
    private $array = array();

    /**
     * @var callable
     */
    private $serialize;

    /**
     * @var callable
     */
    private $unserialize;

    /**
     * Creates a new store.
     *
     * @param array $array The values to set initially in the store.
     */
    public function __construct(array $array = array(), $flags = 0)
    {
        if ($flags & self::SERIALIZE) {
            $this->serialize = array(
                'Webmozart\KeyValueStore\Util\Serializer',
                'serialize'
            );
            $this->unserialize = array(
                'Webmozart\KeyValueStore\Util\Serializer',
                'unserialize'
            );
        } else {
            $this->serialize = function ($value) { return $value; };
            $this->unserialize = function ($value) { return $value; };
        }

        foreach ($array as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        KeyUtil::validate($key);

        $this->array[$key] = call_user_func($this->serialize, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        KeyUtil::validate($key);

        if (!array_key_exists($key, $this->array)) {
            return $default;
        }

        return call_user_func($this->unserialize, $this->array[$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrFail($key)
    {
        KeyUtil::validate($key);

        if (!array_key_exists($key, $this->array)) {
            throw NoSuchKeyException::forKey($key);
        }

        return call_user_func($this->unserialize, $this->array[$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function getMultiple(array $keys, $default = null)
    {
        KeyUtil::validateMultiple($keys);

        $values = array();

        foreach ($keys as $key) {
            $values[$key] = array_key_exists($key, $this->array)
                ? call_user_func($this->unserialize, $this->array[$key])
                : $default;
        }

        return $values;
    }

    /**
     * {@inheritdoc}
     */
    public function getMultipleOrFail(array $keys)
    {
        KeyUtil::validateMultiple($keys);

        $notFoundKeys = array_diff($keys, array_keys($this->array));

        if (count($notFoundKeys) > 0) {
            throw NoSuchKeyException::forKeys($notFoundKeys);
        }

        return $this->getMultiple($keys);
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
    public function exists($key)
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
        return array_map($this->unserialize, $this->array);
    }

    /**
     * {@inheritdoc}
     */
    public function sort($flags = SORT_REGULAR)
    {
        ksort($this->array, $flags);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->array);
    }
}
