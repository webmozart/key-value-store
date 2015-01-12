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

use Memcached;
use Webmozart\KeyValueStore\MemcachedStore;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class MemcachedStoreTest extends AbstractKeyValueStoreTest
{
    private static $supported;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        if (!class_exists('\Memcached')) {
            self::$supported = false;

            return;
        }

        // try to connect
        $memcached = new Memcached();
        $memcached->addServer('127.0.0.1', 11211);
        $memcached->get('foobar');

        if (Memcached::RES_NOTFOUND !== $memcached->getResultCode()) {
            self::$supported = false;

            return;
        }

        self::$supported = true;
    }

    protected function setUp()
    {
        if (!self::$supported) {
            $this->markTestSkipped('Memcached is not supported.');
        }

        parent::setUp();
    }

    protected function createStore()
    {
        return new MemcachedStore();
    }
}
