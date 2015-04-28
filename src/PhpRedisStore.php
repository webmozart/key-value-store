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
use Redis;
use Webmozart\KeyValueStore\Api\KeyValueStore;
use Webmozart\KeyValueStore\Api\NoSuchKeyException;
use Webmozart\KeyValueStore\Api\ReadException;
use Webmozart\KeyValueStore\Api\WriteException;
use Webmozart\KeyValueStore\Assert\Assert;
use Webmozart\KeyValueStore\Util\KeyUtil;
use Webmozart\KeyValueStore\Util\Serializer;

/**
 * A key-value store that uses the PhpRedis extension to connect to a Redis instance.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 * @author Philipp Wahala <philipp.wahala@gmail.com>
 * @link https://github.com/phpredis/phpredis
 */
class PhpRedisStore implements KeyValueStore
{
    /**
     * @var Redis
     */
    private $client;

    /**
     * Creates a store backed by a PhpRedis client.
     *
     * If no client is passed, a new one is created using the default server
     * "127.0.0.1" and the default port 6379.
     *
     * @param Redis $client The client used to connect to Redis.
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
    public function set($key, $value)
    {
        KeyUtil::validate($key);

        $serialized = Serializer::serialize($value);

        try {
            $this->client->set($key, $serialized);
        } catch (Exception $e) {
            throw WriteException::forException($e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        KeyUtil::validate($key);

        $serialized = null;

        try {
            if ($this->client->exists($key)) {
                $serialized = $this->client->get($key);
            }
        } catch (Exception $e) {
            throw ReadException::forException($e);
        }

        if (null === $serialized) {
            throw NoSuchKeyException::forKey($key);
        }

        return Serializer::unserialize($serialized);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        KeyUtil::validate($key);

        try {
            return (bool) $this->client->del($key);
        } catch (Exception $e) {
            throw WriteException::forException($e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        KeyUtil::validate($key);

        try {
            return $this->client->exists($key);
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
            $this->client->flushdb();
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
            return $this->client->keys('*');
        } catch (Exception $e) {
            throw ReadException::forException($e);
        }
    }
}
