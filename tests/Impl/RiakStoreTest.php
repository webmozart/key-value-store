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

use Basho\Riak\Riak;
use Webmozart\KeyValueStore\Impl\RiakStore;
use Webmozart\KeyValueStore\Tests\AbstractKeyValueStoreTest;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class RiakStoreTest extends AbstractKeyValueStoreTest
{
    private static $supported;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        $client = new Riak();

        self::$supported = $client->isAlive();
    }

    protected function setUp()
    {
        if (!self::$supported) {
            $this->markTestSkipped('Riak is not running.');
        }

        parent::setUp();
    }

    protected function createStore()
    {
        return new RiakStore('test-bucket');
    }
}
