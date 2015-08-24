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

use Redis;

/**
 * A key-value store that uses the PhpRedis extension to connect to a Redis instance.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 * @author Philipp Wahala <philipp.wahala@gmail.com>
 * @author Titouan Galopin <galopintitouan@gmail.com>
 *
 * @link https://github.com/phpredis/phpredis
 */
class PhpRedisStore extends AbstractRedisStore
{
    /**
     * Creates a store backed by a PhpRedis client.
     *
     * If no client is passed, a new one is created using the default server
     * "127.0.0.1" and the default port 6379.
     *
     * @param Redis|null $client The client used to connect to Redis.
     */
    public function __construct(Redis $client = null)
    {
        if (null === $client) {
            $client = new Redis();
            $client->connect('127.0.0.1', 6379);
        }

        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    protected function clientNotFoundValue()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function clientGet($key)
    {
        return $this->client->get($key);
    }

    /**
     * {@inheritdoc}
     */
    protected function clientGetMultiple(array $keys)
    {
        return $this->client->getMultiple($keys);
    }

    /**
     * {@inheritdoc}
     */
    protected function clientSet($key, $value)
    {
        $this->client->set($key, $value);
    }

    /**
     * {@inheritdoc}
     */
    protected function clientRemove($key)
    {
        return (bool) $this->client->del($key);
    }

    /**
     * {@inheritdoc}
     */
    protected function clientExists($key)
    {
        return (bool) $this->client->exists($key);
    }

    /**
     * {@inheritdoc}
     */
    protected function clientClear()
    {
        $this->client->flushdb();
    }

    /**
     * {@inheritdoc}
     */
    protected function clientKeys()
    {
        return $this->client->keys('*');
    }
}
