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

use ArrayIterator;
use Exception;
use MongoDB\Client;
use Webmozart\KeyValueStore\MongoDbStore;
use Webmozart\KeyValueStore\Tests\Fixtures\TestException;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
abstract class AbstractMongoDbStoreTest extends AbstractKeyValueStoreTest
{
    const DATABASE_NAME = 'webmozart-key-value-store-test-db';

    const COLLECTION_NAME = 'test-collection';

    private static $supported;

    /**
     * @var Client
     */
    protected $client;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();


        try {
            $client = new Client();
            $client->listDatabases();

            self::$supported = true;
        } catch (Exception $e) {
            self::$supported = false;
        }
    }

    protected function setUp()
    {
        if (!self::$supported) {
            $this->markTestSkipped('MongoDB is not running.');
        }

        $this->client = new Client();

        parent::setUp();
    }

    protected function tearDown()
    {
        if (!self::$supported) {
            return;
        }

        parent::tearDown();

        $this->client->dropDatabase(self::DATABASE_NAME);
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\WriteException
     * @expectedExceptionMessage I failed!
     */
    public function testSetThrowsWriteExceptionIfWriteFails()
    {
        $exception = new TestException('I failed!');

        $collection = $this->getMockBuilder('MongoDB\Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $collection->expects($this->once())
            ->method('replaceOne')
            ->willThrowException($exception);

        $store = new MongoDbStore($collection);
        $store->set('key', 'value');
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\WriteException
     * @expectedExceptionMessage I failed!
     */
    public function testRemoveThrowsWriteExceptionIfWriteFails()
    {
        $exception = new TestException('I failed!');

        $collection = $this->getMockBuilder('MongoDB\Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $collection->expects($this->once())
            ->method('deleteOne')
            ->willThrowException($exception);

        $store = new MongoDbStore($collection);
        $store->remove('key');
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\WriteException
     * @expectedExceptionMessage I failed!
     */
    public function testRemoveThrowsWriteExceptionIfGetDeletedCountFails()
    {
        $exception = new TestException('I failed!');

        $collection = $this->getMockBuilder('MongoDB\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $result = $this->getMockBuilder('MongoDB\DeleteResult')
            ->disableOriginalConstructor()
            ->getMock();

        $collection->expects($this->once())
            ->method('deleteOne')
            ->willReturn($result);

        $result->expects($this->once())
            ->method('getDeletedCount')
            ->willThrowException($exception);

        $store = new MongoDbStore($collection);
        $store->remove('key');
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\WriteException
     * @expectedExceptionMessage I failed!
     */
    public function testClearThrowsWriteExceptionIfWriteFails()
    {
        $exception = new TestException('I failed!');

        $collection = $this->getMockBuilder('MongoDB\Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $collection->expects($this->once())
            ->method('drop')
            ->willThrowException($exception);

        $store = new MongoDbStore($collection);
        $store->clear();
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\ReadException
     * @expectedExceptionMessage I failed!
     */
    public function testGetThrowsReadExceptionIfReadFails()
    {
        $exception = new TestException('I failed!');

        $collection = $this->getMockBuilder('MongoDB\Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $collection->expects($this->once())
            ->method('findOne')
            ->willThrowException($exception);

        $store = new MongoDbStore($collection);
        $store->get('key');
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\UnserializationFailedException
     */
    public function testGetThrowsExceptionIfNotUnserializable()
    {
        $collection = $this->getMockBuilder('MongoDB\Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $collection->expects($this->once())
            ->method('findOne')
            ->willReturn(array('_id' => 'key', 'value' => 'foobar'));

        $store = new MongoDbStore($collection);
        $store->get('key');
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\ReadException
     * @expectedExceptionMessage I failed!
     */
    public function testGetOrFailThrowsReadExceptionIfReadFails()
    {
        $exception = new TestException('I failed!');

        $collection = $this->getMockBuilder('MongoDB\Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $collection->expects($this->once())
            ->method('findOne')
            ->willThrowException($exception);

        $store = new MongoDbStore($collection);
        $store->getOrFail('key');
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\UnserializationFailedException
     */
    public function testGetOrFailThrowsExceptionIfNotUnserializable()
    {
        $collection = $this->getMockBuilder('MongoDB\Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $collection->expects($this->once())
            ->method('findOne')
            ->willReturn(array('_id' => 'key', 'value' => 'foobar'));

        $store = new MongoDbStore($collection);
        $store->getOrFail('key');
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\ReadException
     * @expectedExceptionMessage I failed!
     */
    public function testGetMultipleThrowsReadExceptionIfReadFails()
    {
        $exception = new TestException('I failed!');

        $collection = $this->getMockBuilder('MongoDB\Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $collection->expects($this->once())
            ->method('find')
            ->willThrowException($exception);

        $store = new MongoDbStore($collection);
        $store->getMultiple(array('key'));
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\UnserializationFailedException
     */
    public function testGetMultipleThrowsExceptionIfNotUnserializable()
    {
        $collection = $this->getMockBuilder('MongoDB\Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $cursor = new ArrayIterator(array(
            array('_id' => 'key', 'value' => 'foobar'),
        ));

        $collection->expects($this->once())
            ->method('find')
            ->willReturn($cursor);

        $store = new MongoDbStore($collection);
        $store->getMultiple(array('key'));
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\ReadException
     * @expectedExceptionMessage I failed!
     */
    public function testGetMultipleOrFailThrowsReadExceptionIfReadFails()
    {
        $exception = new TestException('I failed!');

        $collection = $this->getMockBuilder('MongoDB\Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $collection->expects($this->once())
            ->method('find')
            ->willThrowException($exception);

        $store = new MongoDbStore($collection);
        $store->getMultipleOrFail(array('key'));
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\UnserializationFailedException
     */
    public function testGetMultipleOrFailThrowsExceptionIfNotUnserializable()
    {
        $collection = $this->getMockBuilder('MongoDB\Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $cursor = new ArrayIterator(array(
            array('_id' => 'key', 'value' => 'foobar'),
        ));

        $collection->expects($this->once())
            ->method('find')
            ->willReturn($cursor);

        $store = new MongoDbStore($collection);
        $store->getMultipleOrFail(array('key'));
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\ReadException
     * @expectedExceptionMessage I failed!
     */
    public function testExistsThrowsReadExceptionIfReadFails()
    {
        $exception = new TestException('I failed!');

        $collection = $this->getMockBuilder('MongoDB\Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $collection->expects($this->once())
            ->method('count')
            ->willThrowException($exception);

        $store = new MongoDbStore($collection);
        $store->exists('key');
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\ReadException
     * @expectedExceptionMessage I failed!
     */
    public function testKeysThrowsReadExceptionIfReadFails()
    {
        $exception = new TestException('I failed!');

        $collection = $this->getMockBuilder('MongoDB\Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $collection->expects($this->once())
            ->method('find')
            ->willThrowException($exception);

        $store = new MongoDbStore($collection);
        $store->keys();
    }
}
