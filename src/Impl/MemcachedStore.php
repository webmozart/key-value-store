<?php

/*
 * This file is part of the webmozart/key-value-store package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\KeyValueStore\Impl;

use Exception;
use Memcache;
use Memcached;
use RuntimeException;
use Webmozart\KeyValueStore\Assert\Assertion;
use Webmozart\KeyValueStore\KeyValueStore;
use Webmozart\KeyValueStore\SerializationFailedException;

/**
 * A key-value store backed by a Memcached instance.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class MemcachedStore implements KeyValueStore
{
    /**
     * @var Memcache
     */
    private $client;

    /**
     * Creates the store using the given Memcached instance.
     *
     * If no instance is passed, a new instance is created connecting to
     * "127.0.0.1" and the default port 11211.
     *
     * @param Memcached $client The Memcached client.
     */
    public function __construct(Memcached $client = null)
    {
        if (null === $client) {
            $client = new Memcached();
            $client->addServer('127.0.0.1', 11211);
        }

        $this->client = $client;
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
            throw SerializationFailedException::forException($e);
        }

        $this->client->set($key, $serialized);
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        Assertion::key($key);

        if (false === ($serialized = $this->client->get($key))) {
            if (Memcached::RES_NOTFOUND === $this->client->getResultCode()) {
                return $default;
            }

            throw new RuntimeException($this->client->getResultMessage(), $this->client->getResultCode());
        }

        return unserialize($serialized);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        Assertion::key($key);

        return $this->client->delete($key);
    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        Assertion::key($key);

        return false !== $this->client->get($key);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->client->flush();
    }
}
