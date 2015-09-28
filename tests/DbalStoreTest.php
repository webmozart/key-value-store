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

use Doctrine\DBAL\DriverManager;
use Webmozart\KeyValueStore\Api\KeyValueStore;
use Webmozart\KeyValueStore\Api\SortableStore;
use Webmozart\KeyValueStore\DbalStore;
use Webmozart\KeyValueStore\Tests\Fixtures\TestException;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 * @author Michiel Boeckaert <boeckaert@gmail.com>
 */
class DbalStoreTest extends AbstractKeyValueStoreTest
{
    static protected $dbalStore;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        $connection       = DriverManager::getConnection(array('driver' => 'pdo_sqlite', 'memory' => true));
        $schemaManager    = $connection->getSchemaManager();
        $schema           = $schemaManager->createSchema();
        self::$dbalStore = new DbalStore($connection, 'store');
        $table = self::$dbalStore->configureSchema($schema);

        if (null !== $table) {
            $schemaManager->createTable($table);
        }
    }

    /**
     * @return KeyValueStore|SortableStore The created store.
     */
    protected function createStore()
    {
        return self::$dbalStore;
    }

    public function testSettingAValueTwiceUpdatesTheValue()
    {
        $this->store->set('a', '123');
        $this->store->set('a', '124');

        $this->assertEquals('124', $this->store->get('a'));
    }

    /**
     * @test
     */
    public function testSettingNullWorks()
    {
        $this->store->set('beez', null);
        $this->assertTrue($this->store->exists('beez'));
        $this->assertNull($this->store->get('beez', 'hello'));
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\WriteException
     */
    public function testSetThrowsWriteExceptionIfWriteFails()
    {
        $connection = $this->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();

        $connection->expects($this->once())->method('fetchAssoc')->willThrowException(new TestException('I failed'));
        $store = new DbalStore($connection, 'store');
        $store->set('foo', 'bar');
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\WriteException
     */
    public function testRemoveThrowsWriteExceptionIfWriteFails()
    {
        $connection = $this->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();

        $connection->expects($this->once())->method('delete')->willThrowException(new TestException('I failed'));
        $store = new DbalStore($connection, 'store');
        $store->remove('foo');
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\WriteException
     */
    public function testClearThrowsWriteExceptionIfWriteFails()
    {
        $connection = $this->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();

        $connection->expects($this->once())->method('query')->willThrowException(new TestException('I failed'));
        $store = new DbalStore($connection, 'store');
        $store->clear();
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\ReadException
     */
    public function testGetThrowsReadExceptionIfReadFails()
    {
        $connection = $this->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();

        $connection->expects($this->once())->method('fetchColumn')->willThrowException(new TestException('I failed'));
        $store = new DbalStore($connection, 'store');
        $store->get('foo');
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\UnserializationFailedException
     */
    public function testGetThrowsExceptionIfNotUnserializable()
    {
        $connection = $this->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();

        $connection->expects($this->once())->method('fetchColumn')->willReturn('foo_bar');
        $store = new DbalStore($connection, 'store');
        $store->get('foo');
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\ReadException
     */
    public function testGetOrFailThrowsReadExceptionIfReadFails()
    {
        $connection = $this->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();

        $connection->expects($this->once())->method('fetchColumn')->willThrowException(new TestException('I failed'));
        $store = new DbalStore($connection, 'store');
        $store->getOrFail('foo');
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\UnserializationFailedException
     */
    public function testGetOrFailThrowsExceptionIfNotUnserializable()
    {
        $connection = $this->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();

        $connection->expects($this->once())->method('fetchColumn')->willReturn('foo_bar');
        $store = new DbalStore($connection, 'store');
        $store->getOrFail('foo');
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\ReadException
     */
    public function testGetMultipleThrowsReadExceptionIfReadFails()
    {
        $connection = $this->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();

        $connection->expects($this->once())->method('executeQuery')->willThrowException(new TestException('I failed'));
        $store = new DbalStore($connection, 'store');
        $store->getMultiple(array('foo', 'bar'));
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\UnserializationFailedException
     */
    public function testGetMultipleThrowsExceptionIfNotUnserializable()
    {
        $connection = $this->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();

        $statement = $this->getMockBuilder('Doctrine\DBAL\Driver\Statement')
            ->disableOriginalConstructor()
            ->getMock();

        $connection->expects($this->once())->method('executeQuery')->willReturn($statement);

        $statement->expects($this->once())->method('fetchAll')->willReturn(array(array('key' => 'my_key', 'value' => 'foo_bar')));
        $store = new DbalStore($connection, 'store');
        $store->getMultiple(['foo', 'bar']);
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\ReadException
     */
    public function testGetMultipleOrFailThrowsReadExceptionIfReadFails()
    {
        $connection = $this->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();

        $connection->expects($this->once())->method('executeQuery')->willThrowException(new TestException('I failed'));
        $store = new DbalStore($connection, 'store');
        $store->getMultiple(['foo', 'bar']);
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\UnserializationFailedException
     */
    public function testGetMultipleOrFailThrowsExceptionIfNotUnserializable()
    {
        $connection = $this->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();

        $statement = $this->getMockBuilder('Doctrine\DBAL\Driver\Statement')
            ->disableOriginalConstructor()
            ->getMock();

        $connection->expects($this->once())->method('executeQuery')->willReturn($statement);

        $statement->expects($this->once())->method('fetchAll')->willReturn(array(array('key' => 'my_key', 'value' => 'foo_bar')));

        $store = new DbalStore($connection, 'store');
        $store->getMultipleOrFail(['foo', 'bar']);
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\ReadException
     */
    public function testExistsThrowsReadExceptionIfReadFails()
    {
        $connection = $this->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();

        $connection->expects($this->once())->method('fetchAssoc')->willThrowException(new TestException('I failed'));

        $store = new DbalStore($connection, 'store');
        $store->exists('foo');
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\ReadException
     */
    public function testKeysThrowsReadExceptionIfReadFails()
    {
        $connection = $this->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();

        $connection->expects($this->once())->method('query')->willThrowException(new TestException('I failed'));
        $store = new DbalStore($connection, 'store');
        $store->keys();
    }
}