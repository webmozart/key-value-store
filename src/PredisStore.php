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

use Predis\Client;
use Predis\ClientInterface;

/**
 * A key-value store that uses Predis to connect to a Redis instance.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
class PredisStore extends AbstractRedisStore
{
    /**
     * Creates a store backed by a Predis client.
     *
     * If no client is passed, a new one is created using the default server
     * "127.0.0.1" and the default port 6379.
     *
     * @param ClientInterface|null $client The client used to connect to Redis.
     */
    public function __construct(ClientInterface $client = null)
    {
        $this->client = $client ?: new Client();
    }

    /**
     * @inheritdoc
     */
    protected function clientNotFoundValue()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    protected function clientGet($key)
    {
        return $this->client->get($key);
    }

    /**
     * @inheritdoc
     */
    protected function clientGetMultiple(array $keys)
    {
        return $this->client->mget($keys);
    }

    /**
     * @inheritdoc
     */
    protected function clientSet($key, $value)
    {
        $this->client->set($key, $value);
    }

    /**
     * @inheritdoc
     */
    protected function clientRemove($key)
    {
        return (bool) $this->client->del($key);
    }

    /**
     * @inheritdoc
     */
    protected function clientExists($key)
    {
        return (bool) $this->client->exists($key);
    }

    /**
     * @inheritdoc
     */
    protected function clientClear()
    {
        $this->client->flushdb();
    }

    /**
     * @inheritdoc
     */
    protected function clientKeys()
    {
        return $this->client->keys('*');
    }
}
