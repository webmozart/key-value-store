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

use Exception;
use Predis\Client;
use Predis\ClientInterface;
use Webmozart\KeyValueStore\Assert\Assertion;
use Webmozart\KeyValueStore\InvalidValueException;
use Webmozart\KeyValueStore\KeyValueStore;
use Webmozart\KeyValueStore\Purgeable;

/**
 * A key-value store backed by a Redis instance.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class RedisStore implements KeyValueStore, Purgeable
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * Creates a store backed by a Redis client.
     *
     * If no client is passed, a new one is created using the default server
     * "127.0.0.1" and the default port 6379.
     *
     * @param ClientInterface $client The client used to connect to Redis.
     */
    public function __construct(ClientInterface $client = null)
    {
        $this->client = $client ?: new Client();
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

        return $this->client->exists($key)
            ? unserialize($this->client->get($key))
            : $default;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        Assertion::key($key);

        return (bool) $this->client->del($key);
    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        Assertion::key($key);

        return $this->client->exists($key);
    }

    /**
     * {@inheritdoc}
     */
    public function purge()
    {
        $this->client->flushdb();
    }
}
