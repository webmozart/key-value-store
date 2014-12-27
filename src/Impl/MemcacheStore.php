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
use Webmozart\KeyValueStore\Assert\Assertion;
use Webmozart\KeyValueStore\InvalidValueException;
use Webmozart\KeyValueStore\KeyValueStore;
use Webmozart\KeyValueStore\Purgeable;

/**
 * A key-value store backed by a Memcache instance.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class MemcacheStore implements KeyValueStore, Purgeable
{
    /**
     * @var Memcache
     */
    private $client;

    /**
     * Creates the store using the given Memcache instance.
     *
     * If no instance is passed, a new instance is created connecting to
     * "127.0.0.1" and the default port.
     *
     * @param Memcache $client The Memcache client.
     */
    public function __construct(Memcache $client = null)
    {
        if (null === $client) {
            $client = new Memcache();
            $client->connect('127.0.0.1');
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
            throw InvalidValueException::forException($e);
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
            return $default;
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
    public function purge()
    {
        $this->client->flush();
    }
}
