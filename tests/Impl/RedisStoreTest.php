<?php

/*
 * This file is part of the webmozart/key-value-store package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\KeyValueStore\Tests\Impl;

use Predis\Client;
use Predis\Connection\ConnectionException;
use Webmozart\KeyValueStore\Impl\RedisStore;
use Webmozart\KeyValueStore\Tests\AbstractKeyValueStoreTest;
use Webmozart\KeyValueStore\Tests\PurgeableTestTrait;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class RedisStoreTest extends AbstractKeyValueStoreTest
{
    use PurgeableTestTrait;

    private static $supported;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        $client = new Client();

        try {
            $client->connect();
            self::$supported = true;
        } catch (ConnectionException $e) {
            self::$supported = false;
        }
    }

    protected function setUp()
    {
        if (!self::$supported) {
            $this->markTestSkipped('Redis is not running.');
        }

        parent::setUp();
    }

    protected function createStore()
    {
        return new RedisStore();
    }
}
