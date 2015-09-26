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
 * A key-value store backed by a PHP array with serialized entries.
 *
 * The contents of the store are lost when the store is released from memory.
 *
 * This store behaves more like persistent key-value stores than
 * {@link ArrayStore}. It is useful for testing.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class SerializingArrayStore extends ArrayStore
{
    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        parent::set($key, serialize($value));
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        if (!$this->exists($key)) {
            return $default;
        }

        return unserialize(parent::get($key));
    }

    /**
     * {@inheritdoc}
     */
    public function getOrFail($key)
    {
        return unserialize(parent::getOrFail($key));
    }

    /**
     * {@inheritdoc}
     */
    public function getMultiple(array $keys, $default = null)
    {
        $values = parent::getMultiple($keys, $default);

        foreach ($values as $key => $value) {
            if ($this->exists($key)) {
                $values[$key] = unserialize($value);
            }
        }

        return $values;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        $values = parent::toArray();

        foreach ($values as $key => $value) {
            $values[$key] = unserialize($value);
        }

        return $values;
    }
}
