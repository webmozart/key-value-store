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

use Basho\Riak\Riak;
use Exception;
use Webmozart\KeyValueStore\Api\KeyValueStore;
use Webmozart\KeyValueStore\Api\NoSuchKeyException;
use Webmozart\KeyValueStore\Api\ReadException;
use Webmozart\KeyValueStore\Api\WriteException;
use Webmozart\KeyValueStore\Assert\Assert;
use Webmozart\KeyValueStore\Util\KeyUtil;
use Webmozart\KeyValueStore\Util\Serializer;

/**
 * A key-value store backed by a Riak client.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class RiakStore implements KeyValueStore
{
    /**
     * @var string
     */
    private $bucketName;

    /**
     * @var Riak
     */
    private $client;

    /**
     * Creates a store backed by a Riak client.
     *
     * If no client is passed, a new one is created using the default server
     * "127.0.0.1" and the default port 8098.
     *
     * @param string $bucketName The name of the Riak bucket to use.
     * @param Riak   $client     The client used to connect to Riak.
     */
    public function __construct($bucketName, Riak $client = null)
    {
        $this->bucketName = $bucketName;
        $this->client = $client ?: new Riak();
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        KeyUtil::validate($key);

        $serialized = Serializer::serialize($value);

        try {
            $this->client->bucket($this->bucketName)->newBinary($key, $serialized)->store();
        } catch (Exception $e) {
            throw WriteException::forException($e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        KeyUtil::validate($key);

        try {
            $object = $this->client->bucket($this->bucketName)->getBinary($key);
            $exists = $object->exists();
        } catch (Exception $e) {
            throw ReadException::forException($e);
        }

        if (!$exists) {
            return $default;
        }

        return Serializer::unserialize($object->getData());
    }

    /**
     * {@inheritdoc}
     */
    public function getOrFail($key)
    {
        KeyUtil::validate($key);

        try {
            $object = $this->client->bucket($this->bucketName)->getBinary($key);
            $exists = $object->exists();
        } catch (Exception $e) {
            throw ReadException::forException($e);
        }

        if (!$exists) {
            throw NoSuchKeyException::forKey($key);
        }

        return Serializer::unserialize($object->getData());
    }

    /**
     * {@inheritdoc}
     */
    public function getMultiple(array $keys, $default = null)
    {
        KeyUtil::validateMultiple($keys);

        $values = array();

        try {
            $bucket = $this->client->bucket($this->bucketName);

            foreach ($keys as $key) {
                $object = $bucket->getBinary($key);

                $values[$key] = $object->exists() ? $object->getData() : false;
            }
        } catch (Exception $e) {
            throw ReadException::forException($e);
        }

        foreach ($values as $key => $value) {
            $values[$key] = false === $value
                ? $default
                : Serializer::unserialize($value);
        }

        return $values;
    }

    /**
     * {@inheritdoc}
     */
    public function getMultipleOrFail(array $keys)
    {
        KeyUtil::validateMultiple($keys);

        $values = array();
        $notFoundKeys = array();

        try {
            $bucket = $this->client->bucket($this->bucketName);

            foreach ($keys as $key) {
                $values[$key] = $bucket->getBinary($key);

                if (!$values[$key]->exists()) {
                    $notFoundKeys[] = $key;
                }
            }
        } catch (Exception $e) {
            throw ReadException::forException($e);
        }

        if ($notFoundKeys) {
            throw NoSuchKeyException::forKeys($notFoundKeys);
        }

        foreach ($values as $key => $object) {
            $values[$key] = Serializer::unserialize($object->getData());
        }

        return $values;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        KeyUtil::validate($key);

        try {
            $object = $this->client->bucket($this->bucketName)->get($key);

            if (!$object->exists()) {
                return false;
            }

            $object->delete();
        } catch (Exception $e) {
            throw WriteException::forException($e);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function exists($key)
    {
        KeyUtil::validate($key);

        try {
            return $this->client->bucket($this->bucketName)->get($key)->exists();
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
            $bucket = $this->client->bucket($this->bucketName);

            foreach ($bucket->getKeys() as $key) {
                $bucket->get($key)->delete();
            }
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
            return $this->client->bucket($this->bucketName)->getKeys();
        } catch (Exception $e) {
            throw ReadException::forException($e);
        }
    }
}
