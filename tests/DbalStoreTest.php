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

use Doctrine\DBAL\Connection;
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
    protected static $dbalStore;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        $connection = DriverManager::getConnection(array('driver' => 'pdo_sqlite', 'memory' => true));
        $schemaManager = $connection->getSchemaManager();
        $schema = $schemaManager->createSchema();
        self::$dbalStore = new DbalStore($connection, 'store');

        if (!$schema->hasTable(self::$dbalStore->getTableName())) {
            $schemaManager->createTable(self::$dbalStore->getTableForCreate());
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

    public function provideUnsafeTableNamesSoWeCanBlowUpOurDataBase()
    {
        return array(
            array(null),
            array(false),
            array(array()),
            array(''),
        );
    }

    /**
     * @dataProvider provideUnsafeTableNamesSoWeCanBlowUpOurDataBase
     * @expectedException \InvalidArgumentException
     */
    public function testTheTableNameNeedsToBeANotEmptyString($tableName)
    {
        $connection = $this->getConnectionMock();
        new DbalStore($connection, $tableName);
    }

    public function testGetTableNameWorks()
    {
        $connection = $this->getConnectionMock();
        $store = new DbalStore($connection, 'foo');
        $this->assertEquals('foo', $store->getTableName());
    }

    public function testGetTableNameDefaultsToStore()
    {
        $connection = $this->getConnectionMock();
        $store = new DbalStore($connection);
        $this->assertEquals('store', $store->getTableName());
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\WriteException
     */
    public function testSetThrowsWriteExceptionIfWriteFails()
    {
        $connection = $this->getConnectionMock();
        $connection->expects($this->once())->method('fetchAssoc')->willThrowException(new TestException('I failed'));

        $store = new DbalStore($connection, 'store');
        $store->set('foo', 'bar');
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\WriteException
     */
    public function testRemoveThrowsWriteExceptionIfWriteFails()
    {
        $connection = $this->getConnectionMock();
        $connection->expects($this->once())->method('delete')->willThrowException(new TestException('I failed'));

        $store = new DbalStore($connection, 'store');
        $store->remove('foo');
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\WriteException
     */
    public function testClearThrowsWriteExceptionIfWriteFails()
    {
        $connection = $this->getConnectionMock();
        $connection->expects($this->once())->method('query')->willThrowException(new TestException('I failed'));

        $store = new DbalStore($connection, 'store');
        $store->clear();
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\ReadException
     */
    public function testGetThrowsReadExceptionIfReadFails()
    {
        $connection = $this->getConnectionMock();
        $connection->expects($this->once())->method('fetchAssoc')->willThrowException(new TestException('I failed'));

        $store = new DbalStore($connection, 'store');
        $store->get('foo');
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\UnserializationFailedException
     */
    public function testGetThrowsExceptionIfNotUnserializable()
    {
        $connection = $this->getConnectionMock();
        $connection->expects($this->once())->method('fetchAssoc')
            ->willReturn(array('meta_key' => 'foo', 'meta_value' => 'foo_bar'));

        $store = new DbalStore($connection, 'store');
        $store->get('foo');
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\ReadException
     */
    public function testGetOrFailThrowsReadExceptionIfReadFails()
    {
        $connection = $this->getConnectionMock();
        $connection->expects($this->once())->method('fetchAssoc')->willThrowException(new TestException('I failed'));

        $store = new DbalStore($connection, 'store');
        $store->getOrFail('foo');
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\UnserializationFailedException
     */
    public function testGetOrFailThrowsExceptionIfNotUnserializable()
    {
        $connection = $this->getConnectionMock();
        $connection->expects($this->once())->method('fetchAssoc')
            ->willReturn(array('meta_key' => 'foo', 'meta_value' => 'foo_bar'));

        $store = new DbalStore($connection, 'store');
        $store->getOrFail('foo');
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\ReadException
     */
    public function testGetMultipleThrowsReadExceptionIfReadFails()
    {
        $connection = $this->getConnectionMock();
        $connection->expects($this->once())->method('executeQuery')->willThrowException(new TestException('I failed'));

        $store = new DbalStore($connection, 'store');
        $store->getMultiple(array('foo', 'bar'));
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\UnserializationFailedException
     */
    public function testGetMultipleThrowsExceptionIfNotUnserializable()
    {
        $connection = $this->getConnectionMock();
        $statement = $this->getStatementMock();
        $connection->expects($this->once())->method('executeQuery')->willReturn($statement);
        $statement->expects($this->once())->method('fetchAll')
            ->willReturn(array(array('meta_key' => 'my_key', 'meta_value' => 'foo_bar')));

        $store = new DbalStore($connection, 'store');
        $store->getMultiple(array('foo', 'bar'));
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\ReadException
     */
    public function testGetMultipleOrFailThrowsReadExceptionIfReadFails()
    {
        $connection = $this->getConnectionMock();
        $connection->expects($this->once())->method('executeQuery')->willThrowException(new TestException('I failed'));

        $store = new DbalStore($connection, 'store');
        $store->getMultiple(array('foo', 'bar'));
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\UnserializationFailedException
     */
    public function testGetMultipleOrFailThrowsExceptionIfNotUnserializable()
    {
        $connection = $this->getConnectionMock();
        $statement = $this->getStatementMock();
        $connection->expects($this->once())->method('executeQuery')->willReturn($statement);
        $statement->expects($this->once())->method('fetchAll')
            ->willReturn(array(array('meta_key' => 'my_key', 'meta_value' => 'foo_bar')));

        $store = new DbalStore($connection, 'store');
        $store->getMultipleOrFail(array('foo', 'bar'));
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\ReadException
     */
    public function testExistsThrowsReadExceptionIfReadFails()
    {
        $connection = $this->getConnectionMock();
        $connection->expects($this->once())->method('fetchAssoc')->willThrowException(new TestException('I failed'));

        $store = new DbalStore($connection, 'store');
        $store->exists('foo');
    }

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\ReadException
     */
    public function testKeysThrowsReadExceptionIfReadFails()
    {
        $connection = $this->getConnectionMock();
        $connection->expects($this->once())->method('query')->willThrowException(new TestException('I failed'));

        $store = new DbalStore($connection, 'store');
        $store->keys();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Connection
     */
    protected function getConnectionMock()
    {
        $connection = $this->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();

        return $connection;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getStatementMock()
    {
        $statement = $this->getMockBuilder('Doctrine\DBAL\Driver\Statement')
            ->disableOriginalConstructor()
            ->getMock();

        return $statement;
    }
}
