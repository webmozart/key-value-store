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
use Webmozart\KeyValueStore\Api\KeyValueStore;
use Webmozart\KeyValueStore\Tests\Fixtures\NotSerializable;
use Webmozart\KeyValueStore\Tests\Fixtures\PrivateProperties;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
abstract class AbstractKeyValueStoreTest extends PHPUnit_Framework_TestCase
{
    const BINARY_INPUT = "\xff\xf0";

    /**
     * @var KeyValueStore
     */
    private $store;

    /**
     * @return KeyValueStore The created store.
     */
    abstract protected function createStore();

    protected function setUp()
    {
        $this->store = $this->createStore();
    }

    protected function tearDown()
    {
        if ($this->store) {
            $this->store->clear();
        }
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\WriteException
     */
    abstract public function testSetThrowsWriteExceptionIfWriteFails();

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\WriteException
     */
    abstract public function testRemoveThrowsWriteExceptionIfWriteFails();

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\WriteException
     */
    abstract public function testClearThrowsWriteExceptionIfWriteFails();

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\ReadException
     */
    abstract public function testGetThrowsReadExceptionIfReadFails();

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\UnserializationFailedException
     */
    abstract public function testGetThrowsExceptionIfNotUnserializable();

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\ReadException
     */
    abstract public function testHasThrowsReadExceptionIfReadFails();

    public function provideValidKeys()
    {
        return array(
            array(0),
            array(1),
            array(2),
            array(1234),
            array('a'),
            array('b'),
            array('a/b'),
        );
    }

    /**
     * @dataProvider provideValidKeys
     */
    public function testSetSupportsIntAndStringKeys($key)
    {
        $this->store->set($key, 1234);

        $this->assertSame(1234, $this->store->get($key));
    }

    public function provideInvalidKeys()
    {
        return array(
            array(new \stdClass()),
            array(array()),
        );
    }

    /**
     * @dataProvider provideInvalidKeys
     * @expectedException \Webmozart\KeyValueStore\Api\InvalidKeyException
     */
    public function testSetFailsIfInvalidKey($key)
    {
        $this->store->set($key, 1234);
    }

    public function provideScalarValues()
    {
        return array(
            array(0),
            array(1),
            array(1234),
            array('a'),
            array('b'),
            array('a/b'),
            array(12.34),
            array(true),
            array(false),
            array(null),
            array(PHP_INT_MAX),
            // large float
            array((float) PHP_INT_MAX),
        );
    }

    public function provideArrayValues()
    {
        return array(
            array(array(1, 2, 3, 4)),
            array(array('foo' => 'bar', 'baz' => 'bam')),
        );
    }

    public function provideObjectValues()
    {
        return array(
            array((object) array('foo' => 'bar', 'baz' => 'bam')),
            // private properties are enclosed by NUL-bytes when serialized,
            // hence the store implementation needs to be binary-safe
            array(new PrivateProperties('foobar')),
        );
    }

    public function provideBinaryValues()
    {
        return array(
            array(self::BINARY_INPUT),
            array(array('foo' => self::BINARY_INPUT)),
            array(new PrivateProperties(self::BINARY_INPUT)),
        );
    }

    /**
     * @dataProvider provideScalarValues
     */
    public function testSetSupportsScalarValues($value)
    {
        $this->store->set('key', $value);

        $this->assertSame($value, $this->store->get('key'));
        $this->assertTrue($this->store->has('key'));
    }

    /**
     * @dataProvider provideArrayValues
     */
    public function testSetSupportsArrayValues($value)
    {
        $this->store->set('key', $value);

        $this->assertSame($value, $this->store->get('key'));
        $this->assertTrue($this->store->has('key'));
    }

    /**
     * @dataProvider provideObjectValues
     */
    public function testSetSupportsObjectValues($value)
    {
        $this->store->set('key', $value);

        $this->assertEquals($value, $this->store->get('key'));
        $this->assertTrue($this->store->has('key'));
    }

    /**
     * @dataProvider provideBinaryValues
     */
    public function testSetSupportsBinaryValues($value)
    {
        $this->store->set('key', $value);

        $this->assertEquals($value, $this->store->get('key'));
        $this->assertTrue($this->store->has('key'));
    }

    public function provideInvalidValues()
    {
        $resource = fopen(__FILE__, 'r');

        return array(
            array($resource),
            array(new NotSerializable()),
        );
    }

    /**
     * @dataProvider provideInvalidValues
     * @expectedException \Webmozart\KeyValueStore\Api\SerializationFailedException
     */
    public function testSetThrowsExceptionIfValueNotSerializable($value)
    {
        $this->store->set('key', $value);
    }

    /**
     * @dataProvider provideInvalidKeys
     * @expectedException \Webmozart\KeyValueStore\Api\InvalidKeyException
     */
    public function testGetFailsIfInvalidKey($key)
    {
        $this->store->get($key);
    }

    public function testGetReturnsDefaultIfKeyNotFound()
    {
        $this->assertSame('bar', $this->store->get('foo', 'bar'));
    }

    /**
     * @dataProvider provideValidKeys
     */
    public function testHas($key)
    {
        $this->assertFalse($this->store->has($key));
        $this->store->set($key, 1234);
        $this->assertTrue($this->store->has($key));
    }

    /**
     * @dataProvider provideInvalidKeys
     * @expectedException \Webmozart\KeyValueStore\Api\InvalidKeyException
     */
    public function testHasFailsIfInvalidKey($key)
    {
        $this->store->has($key);
    }

    /**
     * @dataProvider provideValidKeys
     */
    public function testRemove($key)
    {
        $otherKey = $key === 'foo' ? 'bar' : 'foo';

        $this->store->set($key, 1234);
        $this->store->set($otherKey, 5678);

        $this->assertTrue($this->store->remove($key));
        $this->assertFalse($this->store->remove($key));
    }

    /**
     * @dataProvider provideInvalidKeys
     * @expectedException \Webmozart\KeyValueStore\Api\InvalidKeyException
     */
    public function testRemoveFailsIfInvalidKey($key)
    {
        $this->store->remove($key);
    }

    public function testClear()
    {
        $this->store->set('a', 1234);
        $this->store->set('b', 5678);
        $this->store->set('c', 9123);

        $this->store->clear();

        $this->assertFalse($this->store->has('a'));
        $this->assertFalse($this->store->has('b'));
        $this->assertFalse($this->store->has('c'));
    }

    public function testClearEmpty()
    {
        $this->store->clear();
    }

    public function testClearAndSet()
    {
        $this->store->set('a', 1234);

        $this->store->clear();

        $this->assertFalse($this->store->has('a'));

        $this->store->set('a', 1234);

        $this->assertTrue($this->store->has('a'));
        $this->assertSame(1234, $this->store->get('a'));
    }
}
