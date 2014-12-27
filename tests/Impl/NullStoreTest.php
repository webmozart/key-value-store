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

use PHPUnit_Framework_TestCase;
use Webmozart\KeyValueStore\Impl\NullStore;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class NullStoreTest extends PHPUnit_Framework_TestCase
{
    public function testSet()
    {
        $store = new NullStore();

        $store->set('foo', 'bar');

        $this->assertNull($store->get('foo'));
    }

    public function testGetAlwaysReturnsDefault()
    {
        $store = new NullStore();

        $this->assertSame('bar', $store->get('foo', 'bar'));
    }

    public function testHasAlwaysReturnsFalse()
    {
        $store = new NullStore();

        $store->set('foo', 'bar');

        $this->assertFalse($store->has('foo'));
    }

    public function testRemoveAlwaysReturnsFalse()
    {
        $store = new NullStore();

        $store->set('foo', 'bar');

        $this->assertFalse($store->remove('foo'));
    }
}
