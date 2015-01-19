<?php

/*
 * This file is part of the webmozart/key-value-store package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\KeyValueStore\Tests;

use Redis;
use Webmozart\KeyValueStore\PredisStore;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 * @author Philipp Wahala <philipp.wahala@gmail.com>
 */
class PhpRedisStoreTest extends AbstractKeyValueStoreTest
{
    private static $supported;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        if (!class_exists('\Redis', false)) {
            self::$supported = false;

            return;
        }

        $redis = new Redis();

        if (!$redis->connect('127.0.0.1', 6379)) {
            self::$supported = false;

            return;
        }

        self::$supported = true;

        return;
    }

    protected function setUp()
    {
        if (!self::$supported) {
            $this->markTestSkipped('PhpRedis is not available or Redis is not running.');
        }

        parent::setUp();
    }

    protected function createStore()
    {
        return new PredisStore();
    }
}
