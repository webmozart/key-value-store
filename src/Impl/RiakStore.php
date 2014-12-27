<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\KeyValueStore\Impl;

use Basho\Riak\Riak;
use Exception;
use Webmozart\KeyValueStore\Assert\Assertion;
use Webmozart\KeyValueStore\InvalidValueException;
use Webmozart\KeyValueStore\KeyValueStore;

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
        Assertion::key($key);

        try {
            $serialized = serialize($value);
        } catch (Exception $e) {
            throw InvalidValueException::forException($e);
        }

        $this->client->bucket($this->bucketName)->newObject($key, $serialized)->store();
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        Assertion::key($key);

        $object = $this->client->bucket($this->bucketName)->get($key);

        if (!$object->exists()) {
            return $default;
        }

        return unserialize($object->getData());
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        Assertion::key($key);

        $object = $this->client->bucket($this->bucketName)->get($key);

        if (!$object->exists()) {
            return false;
        }

        $object->delete();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        Assertion::key($key);

        return $this->client->bucket($this->bucketName)->get($key)->exists();
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $bucket = $this->client->bucket($this->bucketName);

        foreach ($bucket->getKeys() as $key) {
            $bucket->get($key)->delete();
        }
    }
}
