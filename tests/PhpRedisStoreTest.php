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
use Webmozart\KeyValueStore\PhpRedisStore;
use Webmozart\KeyValueStore\Tests\Fixtures\TestException;

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

        self::$supported = @$redis->connect('127.0.0.1', 6379);
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
        return new PhpRedisStore();
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\WriteException
     * @expectedExceptionMessage I failed!
     */
    public function testSetThrowsWriteExceptionIfWriteFails()
    {
        $exception = new TestException('I failed!');

        $redis = $this->getMockBuilder('Redis')
            ->disableOriginalConstructor()
            ->getMock();

        $redis->expects($this->once())
            ->method('set')
            ->willThrowException($exception);

        $store = new PhpRedisStore($redis);
        $store->set('key', 'value');
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\WriteException
     * @expectedExceptionMessage I failed!
     */
    public function testRemoveThrowsWriteExceptionIfWriteFails()
    {
        $exception = new TestException('I failed!');

        $redis = $this->getMockBuilder('Redis')
            ->disableOriginalConstructor()
            ->getMock();

        $redis->expects($this->once())
            ->method('del')
            ->willThrowException($exception);

        $store = new PhpRedisStore($redis);
        $store->remove('key');
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\WriteException
     * @expectedExceptionMessage I failed!
     */
    public function testClearThrowsWriteExceptionIfWriteFails()
    {
        $exception = new TestException('I failed!');

        $redis = $this->getMockBuilder('Redis')
            ->disableOriginalConstructor()
            ->getMock();

        $redis->expects($this->once())
            ->method('flushdb')
            ->willThrowException($exception);

        $store = new PhpRedisStore($redis);
        $store->clear();
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\ReadException
     * @expectedExceptionMessage I failed!
     */
    public function testGetThrowsReadExceptionIfReadFails()
    {
        $exception = new TestException('I failed!');

        $redis = $this->getMockBuilder('Redis')
            ->disableOriginalConstructor()
            ->getMock();

        $redis->expects($this->once())
            ->method('get')
            ->willThrowException($exception);

        $store = new PhpRedisStore($redis);
        $store->get('key');
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\UnserializationFailedException
     */
    public function testGetThrowsExceptionIfNotUnserializable()
    {
        $redis = $this->getMockBuilder('Redis')
            ->disableOriginalConstructor()
            ->getMock();

        $redis->expects($this->once())
            ->method('get')
            ->willReturn('foobar');

        $store = new PhpRedisStore($redis);
        $store->get('key');
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\ReadException
     * @expectedExceptionMessage I failed!
     */
    public function testGetMultipleThrowsReadExceptionIfReadFails()
    {
        $exception = new TestException('I failed!');

        $redis = $this->getMockBuilder('Redis')
            ->disableOriginalConstructor()
            ->getMock();

        $redis->expects($this->once())
            ->method('getMultiple')
            ->willThrowException($exception);

        $store = new PhpRedisStore($redis);
        $store->getMultipleOrFail(array('key'));
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\UnserializationFailedException
     */
    public function testGetMultipleThrowsExceptionIfNotUnserializable()
    {
        $redis = $this->getMockBuilder('Redis')
            ->disableOriginalConstructor()
            ->getMock();

        $redis->expects($this->once())
            ->method('getMultiple')
            ->willReturn(array('foobar'));

        $store = new PhpRedisStore($redis);
        $store->getMultipleOrFail(array('key'));
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\ReadException
     * @expectedExceptionMessage I failed!
     */
    public function testExistsThrowsReadExceptionIfReadFails()
    {
        $exception = new TestException('I failed!');

        $redis = $this->getMockBuilder('Redis')
            ->disableOriginalConstructor()
            ->getMock();

        $redis->expects($this->once())
            ->method('exists')
            ->willThrowException($exception);

        $store = new PhpRedisStore($redis);
        $store->exists('key');
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\ReadException
     * @expectedExceptionMessage I failed!
     */
    public function testKeysThrowsReadExceptionIfReadFails()
    {
        $exception = new TestException('I failed!');

        $redis = $this->getMockBuilder('Redis')
            ->disableOriginalConstructor()
            ->getMock();

        $redis->expects($this->once())
            ->method('keys')
            ->willThrowException($exception);

        $store = new PhpRedisStore($redis);
        $store->keys();
    }
}
