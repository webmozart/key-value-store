<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\KeyValueStore;

use Exception;
use Predis\Client;
use Predis\ClientInterface;
use Webmozart\KeyValueStore\Api\KeyValueStore;
use Webmozart\KeyValueStore\Api\SerializationFailedException;
use Webmozart\KeyValueStore\Assert\Assert;

/**
 * A key-value store backed by a Redis instance.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class RedisStore implements KeyValueStore
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
        Assert::key($key);

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
        Assert::key($key);

        return $this->client->exists($key)
            ? unserialize($this->client->get($key))
            : $default;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        Assert::key($key);

        return (bool) $this->client->del($key);
    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        Assert::key($key);

        return $this->client->exists($key);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->client->flushdb();
    }
}
