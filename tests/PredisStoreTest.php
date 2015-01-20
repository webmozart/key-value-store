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

use Predis\Client;
use Predis\Connection\ConnectionException;
use Webmozart\KeyValueStore\PredisStore;
use Webmozart\KeyValueStore\Tests\Fixtures\TestException;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class PredisStoreTest extends AbstractKeyValueStoreTest
{
    private static $supported;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        $client = new Client();

        try {
            $client->connect();
            $client->disconnect();
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
        return new PredisStore();
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\WriteException
     * @expectedExceptionMessage I failed!
     */
    public function testSetThrowsWriteExceptionIfWriteFails()
    {
        $exception = new TestException('I failed!');

        $client = $this->getMock('Predis\ClientInterface');

        $client->expects($this->once())
            ->method('__call')
            ->with('set')
            ->willThrowException($exception);

        $store = new PredisStore($client);
        $store->set('key', 'value');
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\WriteException
     * @expectedExceptionMessage I failed!
     */
    public function testRemoveThrowsWriteExceptionIfWriteFails()
    {
        $exception = new TestException('I failed!');

        $client = $this->getMock('Predis\ClientInterface');

        $client->expects($this->once())
            ->method('__call')
            ->with('del')
            ->willThrowException($exception);

        $store = new PredisStore($client);
        $store->remove('key');
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\WriteException
     * @expectedExceptionMessage I failed!
     */
    public function testClearThrowsWriteExceptionIfWriteFails()
    {
        $exception = new TestException('I failed!');

        $client = $this->getMock('Predis\ClientInterface');

        $client->expects($this->once())
            ->method('__call')
            ->with('flushdb')
            ->willThrowException($exception);

        $store = new PredisStore($client);
        $store->clear();
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\ReadException
     * @expectedExceptionMessage I failed!
     */
    public function testGetThrowsReadExceptionIfReadFails()
    {
        $exception = new TestException('I failed!');

        $client = $this->getMock('Predis\ClientInterface');

        $client->expects($this->at(0))
            ->method('__call')
            ->with('exists')
            ->willReturn(true);

        $client->expects($this->at(1))
            ->method('__call')
            ->with('get')
            ->willThrowException($exception);

        $store = new PredisStore($client);
        $store->get('key');
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\ReadException
     * @expectedExceptionMessage I failed!
     */
    public function testGetThrowsReadExceptionIfExistsFails()
    {
        $exception = new TestException('I failed!');

        $client = $this->getMock('Predis\ClientInterface');

        $client->expects($this->once())
            ->method('__call')
            ->with('exists')
            ->willThrowException($exception);

        $store = new PredisStore($client);
        $store->get('key');
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\UnserializationFailedException
     */
    public function testGetThrowsExceptionIfNotUnserializable()
    {
        $client = $this->getMock('Predis\ClientInterface');

        $client->expects($this->at(0))
            ->method('__call')
            ->with('exists')
            ->willReturn(true);

        $client->expects($this->at(1))
            ->method('__call')
            ->with('get')
            ->willReturn('foobar');

        $store = new PredisStore($client);
        $store->get('key');
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\ReadException
     * @expectedExceptionMessage I failed!
     */
    public function testHasThrowsReadExceptionIfReadFails()
    {
        $exception = new TestException('I failed!');

        $client = $this->getMock('Predis\ClientInterface');

        $client->expects($this->once())
            ->method('__call')
            ->with('exists')
            ->willThrowException($exception);

        $store = new PredisStore($client);
        $store->has('key');
    }
}
