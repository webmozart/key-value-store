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

use PHPUnit_Framework_TestCase;
use Webmozart\KeyValueStore\NullStore;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class NullStoreTest extends PHPUnit_Framework_TestCase
{
    public function testGetAlwaysReturnsDefault()
    {
        $store = new NullStore();

        $store->set('foo', 'bar');

        $this->assertSame('baz', $store->get('foo', 'baz'));
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\NoSuchKeyException
     */
    public function testGetAlwaysThrowsException()
    {
        $store = new NullStore();

        $store->set('foo', 'bar');

        $store->getOrFail('foo');
    }

    public function testGetMultipleAlwaysReturnsDefault()
    {
        $store = new NullStore();

        $store->set('foo', 'bar');

        $this->assertSame(array(
            'foo' => 'default',
            'bar' => 'default',
        ), $store->getMultiple(array('foo', 'bar'), 'default'));
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\NoSuchKeyException
     */
    public function testGetMultipleOrFailAlwaysThrowsException()
    {
        $store = new NullStore();

        $store->set('foo', 'bar');

        $store->getMultipleOrFail(array('foo'));
    }

    public function testExistsAlwaysReturnsFalse()
    {
        $store = new NullStore();

        $store->set('foo', 'bar');

        $this->assertFalse($store->exists('foo'));
    }

    public function testRemoveAlwaysReturnsFalse()
    {
        $store = new NullStore();

        $store->set('foo', 'bar');

        $this->assertFalse($store->remove('foo'));
    }

    public function testKeysAlwaysReturnsEmptyArray()
    {
        $store = new NullStore();

        $store->set('foo', 'bar');

        $this->assertSame(array(), $store->keys());
    }

    public function testClearDoesNothing()
    {
        $store = new NullStore();

        $store->set('foo1', 'bar1');
        $store->set('foo2', 'bar2');
        $store->clear();

        $this->assertSame(array(), $store->keys());
    }

    public function testSortDoesNothing()
    {
        $store = new NullStore();

        $store->set('foo1', 'bar1');
        $store->set('foo2', 'bar2');
        $store->sort();

        $this->assertSame(array(), $store->keys());
    }
}
