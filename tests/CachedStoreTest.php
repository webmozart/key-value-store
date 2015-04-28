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

use Doctrine\Common\Cache\Cache;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use Webmozart\KeyValueStore\Api\InvalidKeyException;
use Webmozart\KeyValueStore\Api\KeyValueStore;
use Webmozart\KeyValueStore\Api\SerializationFailedException;
use Webmozart\KeyValueStore\Api\WriteException;
use Webmozart\KeyValueStore\CachedStore;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class CachedStoreTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject|KeyValueStore
     */
    private $innerStore;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|Cache
     */
    private $cache;

    /**
     * @var CachedStore
     */
    private $store;

    protected function setUp()
    {
        $this->innerStore = $this->getMock('Webmozart\KeyValueStore\Api\KeyValueStore');
        $this->cache = $this->getMock(__NAMESPACE__.'\Fixtures\TestClearableCache');
        $this->store = new CachedStore($this->innerStore, $this->cache);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateFailsIfNeitherClearableNorFlushable()
    {
        $this->cache = $this->getMock('Doctrine\Common\Cache\Cache');

        new CachedStore($this->innerStore, $this->cache);
    }

    public function testSetWritesToCache()
    {
        $this->innerStore->expects($this->once())
            ->method('set')
            ->with('key', 'value');

        $this->cache->expects($this->once())
            ->method('save')
            ->with('key', 'value');

        $this->store->set('key', 'value');
    }

    public function testSetWritesTtlIfGiven()
    {
        $this->store = new CachedStore($this->innerStore, $this->cache, 100);

        $this->innerStore->expects($this->once())
            ->method('set')
            ->with('key', 'value');

        $this->cache->expects($this->once())
            ->method('save')
            ->with('key', 'value', 100);

        $this->store->set('key', 'value');
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\SerializationFailedException
     */
    public function testSetDoesNotWriteToCacheIfKeyValueStoreFails()
    {
        $this->innerStore->expects($this->once())
            ->method('set')
            ->with('key', 'value')
            ->willThrowException(new SerializationFailedException());

        $this->cache->expects($this->never())
            ->method('save');

        $this->store->set('key', 'value');
    }

    public function testGetReturnsFromCacheIfCached()
    {
        $this->cache->expects($this->at(0))
            ->method('contains')
            ->with('key')
            ->willReturn(true);

        $this->innerStore->expects($this->never())
            ->method('has');

        $this->innerStore->expects($this->never())
            ->method('get');

        $this->cache->expects($this->at(1))
            ->method('fetch')
            ->with('key')
            ->willReturn('value');

        $this->assertSame('value', $this->store->get('key'));
    }

    public function testGetWritesToCacheIfNotCached()
    {
        $this->cache->expects($this->at(0))
            ->method('contains')
            ->with('key')
            ->willReturn(false);

        $this->innerStore->expects($this->at(0))
            ->method('has')
            ->with('key')
            ->willReturn(true);

        $this->innerStore->expects($this->at(1))
            ->method('get')
            ->with('key')
            ->willReturn('value');

        $this->cache->expects($this->at(1))
            ->method('save')
            ->with('key', 'value');

        $this->assertSame('value', $this->store->get('key'));
    }

    public function testGetWritesTtlIfNotCached()
    {
        $this->store = new CachedStore($this->innerStore, $this->cache, 100);

        $this->cache->expects($this->at(0))
            ->method('contains')
            ->with('key')
            ->willReturn(false);

        $this->innerStore->expects($this->at(0))
            ->method('has')
            ->with('key')
            ->willReturn(true);

        $this->innerStore->expects($this->at(1))
            ->method('get')
            ->with('key')
            ->willReturn('value');

        $this->cache->expects($this->at(1))
            ->method('save')
            ->with('key', 'value', 100);

        $this->assertSame('value', $this->store->get('key'));
    }

    public function testGetDoesNotWriteCacheIfNotInStore()
    {
        $this->cache->expects($this->once())
            ->method('contains')
            ->with('key')
            ->willReturn(false);

        $this->innerStore->expects($this->once())
            ->method('has')
            ->with('key')
            ->willReturn(false);

        $this->innerStore->expects($this->never())
            ->method('get');

        $this->cache->expects($this->never())
            ->method('save');

        $this->assertSame('default', $this->store->get('key', 'default'));
    }

    public function testHasQueriesCache()
    {
        $this->cache->expects($this->once())
            ->method('contains')
            ->with('key')
            ->willReturn(true);

        $this->innerStore->expects($this->never())
            ->method('has');

        $this->assertTrue($this->store->has('key'));
    }

    /**
     * @dataProvider provideTrueFalse
     */
    public function testHasQueriesStoreIfNotCached($result)
    {
        $this->cache->expects($this->once())
            ->method('contains')
            ->with('key')
            ->willReturn(false);

        $this->innerStore->expects($this->once())
            ->method('has')
            ->with('key')
            ->willReturn($result);

        $this->assertSame($result, $this->store->has('key'));
    }

    public function provideTrueFalse()
    {
        return array(
            array(true),
            array(false),
        );
    }

    public function testRemoveDeletesFromCache()
    {
        $this->innerStore->expects($this->once())
            ->method('remove')
            ->with('key');

        $this->cache->expects($this->once())
            ->method('delete')
            ->with('key');

        $this->store->remove('key');
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\InvalidKeyException
     */
    public function testRemoveDoesNotDeleteFromCacheIfRemovalFails()
    {
        $this->innerStore->expects($this->once())
            ->method('remove')
            ->with('key')
            ->willThrowException(new InvalidKeyException());

        $this->cache->expects($this->never())
            ->method('delete');

        $this->store->remove('key');
    }

    public function testClearDeletesAllFromCache()
    {
        $this->cache = $this->getMock(__NAMESPACE__.'\Fixtures\TestClearableCache');
        $this->store = new CachedStore($this->innerStore, $this->cache);

        $this->innerStore->expects($this->once())
            ->method('clear');

        $this->cache->expects($this->once())
            ->method('deleteAll');

        $this->store->clear();
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\WriteException
     */
    public function testClearDoesNotDeleteAllFromCacheIfClearFails()
    {
        $this->cache = $this->getMock(__NAMESPACE__.'\Fixtures\TestClearableCache');
        $this->store = new CachedStore($this->innerStore, $this->cache);

        $this->innerStore->expects($this->once())
            ->method('clear')
            ->willThrowException(new WriteException());

        $this->cache->expects($this->never())
            ->method('deleteAll');

        $this->store->clear();
    }

    public function testClearFlushesCache()
    {
        $this->cache = $this->getMock(__NAMESPACE__.'\Fixtures\TestFlushableCache');
        $this->store = new CachedStore($this->innerStore, $this->cache);

        $this->innerStore->expects($this->once())
            ->method('clear');

        $this->cache->expects($this->once())
            ->method('flushAll');

        $this->store->clear();
    }

    public function testClearDeletesAllIfFlushableAndClearable()
    {
        $this->cache = $this->getMock(__NAMESPACE__.'\Fixtures\TestClearableFlushableCache');
        $this->store = new CachedStore($this->innerStore, $this->cache);

        $this->innerStore->expects($this->once())
            ->method('clear');

        $this->cache->expects($this->once())
            ->method('deleteAll');

        $this->store->clear();
    }

    public function testKeysForwardsKeysFromStore()
    {
        $this->innerStore->expects($this->once())
            ->method('keys')
            ->willReturn(array('a', 'b', 'c'));

        $this->assertSame(array('a', 'b', 'c'), $this->store->keys());
    }
}
