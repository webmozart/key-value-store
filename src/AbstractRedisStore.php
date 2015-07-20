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

use Exception;
use Webmozart\KeyValueStore\Api\KeyValueStore;
use Webmozart\KeyValueStore\Api\NoSuchKeyException;
use Webmozart\KeyValueStore\Api\ReadException;
use Webmozart\KeyValueStore\Api\WriteException;
use Webmozart\KeyValueStore\Util\KeyUtil;
use Webmozart\KeyValueStore\Util\Serializer;

/**
 * An abstract Redis key-value store to support multiple Redis clients without code duplication.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
abstract class AbstractRedisStore implements KeyValueStore
{
    /**
     * Redis client.
     *
     * @var object
     */
    protected $client;

    /**
     * Return the value corresponding to "not found"
     * for the internal client.
     *
     * @return mixed
     */
    abstract protected function clientNotFoundValue();

    /**
     * Call the internal client method to fetch a key.
     * Don't have to catch the exceptions.
     *
     * @param string $key The key to fetch
     *
     * @return mixed The raw value
     */
    abstract protected function clientGet($key);

    /**
     * Call the internal client method to fetch multiple keys.
     * Don't have to catch the exceptions.
     *
     * @param array $keys The keys to fetch
     *
     * @return array The raw values
     */
    abstract protected function clientGetMultiple(array $keys);

    /**
     * Call the internal client method to set a value associated to a key.
     * Don't have to catch the exceptions.
     *
     * @param string $key
     * @param mixed  $value
     */
    abstract protected function clientSet($key, $value);

    /**
     * Call the internal client method to remove a key.
     * Don't have to catch the exceptions.
     *
     * @param string $key
     *
     * @return bool true if the removal worked, false otherwise
     */
    abstract protected function clientRemove($key);

    /**
     * Call the internal client method to check if a key exists.
     * Don't have to catch the exceptions.
     *
     * @param string $key
     *
     * @return bool
     */
    abstract protected function clientExists($key);

    /**
     * Call the internal client method to clear all the keys.
     * Don't have to catch the exceptions.
     */
    abstract protected function clientClear();

    /**
     * Call the internal client method to fetch all the keys.
     * Don't have to catch the exceptions.
     *
     * @return array The keys
     */
    abstract protected function clientKeys();

    /**
     * {@inheritdoc}
     */
    public function getMultiple(array $keys, $default = null)
    {
        KeyUtil::validateMultiple($keys);

        // Normalize indices of the array
        $keys = array_values($keys);
        $values = array();

        try {
            $serializedValues = $this->clientGetMultiple($keys);
        } catch (Exception $e) {
            throw ReadException::forException($e);
        }

        foreach ($serializedValues as $i => $serializedValue) {
            $values[$keys[$i]] = $this->clientNotFoundValue() === $serializedValue
                ? $default
                : Serializer::unserialize($serializedValue);
        }

        return $values;
    }

    /**
     * {@inheritdoc}
     */
    public function getMultipleOrFail(array $keys)
    {
        KeyUtil::validateMultiple($keys);

        // Normalize indices of the array
        $keys = array_values($keys);
        $values = array();
        $notFoundKeys = array();

        try {
            $serializedValues = $this->clientGetMultiple($keys);
        } catch (Exception $e) {
            throw ReadException::forException($e);
        }

        foreach ($serializedValues as $i => $serializedValue) {
            if ($this->clientNotFoundValue() === $serializedValue) {
                $notFoundKeys[] = $keys[$i];
            } elseif (0 === count($notFoundKeys)) {
                $values[$keys[$i]] = Serializer::unserialize($serializedValue);
            }
        }

        if (0 !== count($notFoundKeys)) {
            throw NoSuchKeyException::forKeys($notFoundKeys);
        }

        return $values;
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        KeyUtil::validate($key);

        try {
            $serialized = $this->clientGet($key);
        } catch (Exception $e) {
            throw ReadException::forException($e);
        }

        if ($this->clientNotFoundValue() === $serialized) {
            return $default;
        }

        return Serializer::unserialize($serialized);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrFail($key)
    {
        KeyUtil::validate($key);

        try {
            $serialized = $this->clientGet($key);
        } catch (Exception $e) {
            throw ReadException::forException($e);
        }

        if ($this->clientNotFoundValue() === $serialized) {
            throw NoSuchKeyException::forKey($key);
        }

        return Serializer::unserialize($serialized);
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        KeyUtil::validate($key);

        $serialized = Serializer::serialize($value);

        try {
            $this->clientSet($key, $serialized);
        } catch (Exception $e) {
            throw WriteException::forException($e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        KeyUtil::validate($key);

        try {
            return (bool) $this->clientRemove($key);
        } catch (Exception $e) {
            throw WriteException::forException($e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function exists($key)
    {
        KeyUtil::validate($key);

        try {
            return $this->clientExists($key);
        } catch (Exception $e) {
            throw ReadException::forException($e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        try {
            $this->clientClear();
        } catch (Exception $e) {
            throw WriteException::forException($e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function keys()
    {
        try {
            return $this->clientKeys();
        } catch (Exception $e) {
            throw ReadException::forException($e);
        }
    }
}
