<?php

/*
 * This file is part of the webmozart/key-value-store package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\KeyValueStore;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Exception;
use PDO;
use Webmozart\Assert\Assert;
use Webmozart\KeyValueStore\Api\KeyValueStore;
use Webmozart\KeyValueStore\Api\NoSuchKeyException;
use Webmozart\KeyValueStore\Api\ReadException;
use Webmozart\KeyValueStore\Api\WriteException;
use Webmozart\KeyValueStore\Util\KeyUtil;
use Webmozart\KeyValueStore\Util\Serializer;

/**
 * A key-value store backed by Doctrine DBAL.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 * @author Michiel Boeckaert <boeckaert@gmail.com>
 */
class DbalStore implements KeyValueStore
{
    private $connection;
    private $tableName;

    /**
     * @param Connection $connection A doctrine connection instance
     * @param string     $tableName  The name of the database table
     */
    public function __construct(Connection $connection, $tableName = 'store')
    {
        Assert::stringNotEmpty($tableName, 'The table must be a string. Got: %s');

        $this->connection = $connection;
        $this->tableName = $tableName;
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        KeyUtil::validate($key);

        try {
            $existing = $this->exists($key);
        } catch (Exception $e) {
            throw WriteException::forException($e);
        }

        if (false === $existing) {
            $this->doInsert($key, $value);
        } else {
            $this->doUpdate($key, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        KeyUtil::validate($key);

        $dbResult = $this->getDbRow($key);

        if (null === $dbResult) {
            return $default;
        }

        return Serializer::unserialize($dbResult['meta_value']);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrFail($key)
    {
        KeyUtil::validate($key);

        $dbResult = $this->getDbRow($key);

        if (null === $dbResult) {
            throw NoSuchKeyException::forKey($key);
        }

        return Serializer::unserialize($dbResult['meta_value']);
    }

    /**
     * {@inheritdoc}
     */
    public function getMultiple(array $keys, $default = null)
    {
        KeyUtil::validateMultiple($keys);

        // Normalize indices of the array
        $keys = array_values($keys);
        $data = $this->doGetMultiple($keys);

        $results = array();
        $resolved = array();
        foreach ($data as $row) {
            $results[$row['meta_key']] = Serializer::unserialize($row['meta_value']);
            $resolved[$row['meta_key']] = $row['meta_key'];
        }

        $notResolvedArr = array_diff($keys, $resolved);
        foreach ($notResolvedArr as $notResolved) {
            $results[$notResolved] = $default;
        }

        return $results;
    }

    /**
     * {@inheritdoc}
     */
    public function getMultipleOrFail(array $keys)
    {
        KeyUtil::validateMultiple($keys);

        // Normalize indices of the array
        $keys = array_values($keys);

        $data = $this->doGetMultiple($keys);

        $results = array();
        $resolved = array();
        foreach ($data as $row) {
            $results[$row['meta_key']] = Serializer::unserialize($row['meta_value']);
            $resolved[] = $row['meta_key'];
        }

        $notResolvedArr = array_diff($keys, $resolved);

        if (!empty($notResolvedArr)) {
            throw NoSuchKeyException::forKeys($notResolvedArr);
        }

        return $results;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        KeyUtil::validate($key);

        try {
            $result = $this->connection->delete($this->tableName, array('meta_key' => $key));
        } catch (Exception $e) {
            throw WriteException::forException($e);
        }

        return $result === 1;
    }

    /**
     * {@inheritdoc}
     */
    public function exists($key)
    {
        KeyUtil::validate($key);

        try {
            $result = $this->connection->fetchAssoc('SELECT * FROM '.$this->tableName.' WHERE meta_key = ?', array($key));
        } catch (Exception $e) {
            throw ReadException::forException($e);
        }

        return $result ? true : false;
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        try {
            $stmt = $this->connection->query('DELETE FROM '.$this->tableName);
            $stmt->execute();
        } catch (Exception $e) {
            throw WriteException::forException($e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function keys()
    {
        try {
            $stmt = $this->connection->query('SELECT meta_key FROM '.$this->tableName);
            $result = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            throw ReadException::forException($e);
        }

        return $result;
    }

    /**
     * The name for our DBAL database table.
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * Object Representation of the table used in this class.
     *
     * @return Table
     */
    public function getTableForCreate()
    {
        $schema = new Schema();

        $table = $schema->createTable($this->getTableName());

        $table->addColumn('id', 'integer', array('autoincrement' => true));
        $table->addColumn('meta_key', 'string', array('length' => 255));
        $table->addColumn('meta_value', 'object');
        $table->setPrimaryKey(array('id'));
        $table->addUniqueIndex(array('meta_key'));

        return $table;
    }

    private function doInsert($key, $value)
    {
        $serialized = Serializer::serialize($value);

        try {
            $this->connection->insert($this->tableName, array(
                'meta_key' => $key,
                'meta_value' => $serialized,
            ));
        } catch (Exception $e) {
            throw WriteException::forException($e);
        }
    }

    private function doUpdate($key, $value)
    {
        $serialized = Serializer::serialize($value);

        try {
            $this->connection->update($this->tableName, array(
                'meta_value' => $serialized,
            ), array(
                'meta_key' => $key,
            ));
        } catch (Exception $e) {
            throw WriteException::forException($e);
        }
    }

    private function doGetMultiple(array $keys)
    {
        try {
            $stmt = $this->connection->executeQuery('SELECT * FROM '.$this->tableName.' WHERE meta_key IN (?)',
                array($keys),
                array(Connection::PARAM_STR_ARRAY)
            );
            $data = $stmt->fetchAll();
        } catch (Exception $e) {
            throw ReadException::forException($e);
        }

        return is_array($data) ? $data : array();
    }

    private function getDbRow($key)
    {
        try {
            $dbResult = $this->connection->fetchAssoc('SELECT meta_value, meta_key FROM '.$this->tableName.' WHERE meta_key = ?', array($key));
        } catch (Exception $e) {
            throw ReadException::forException($e);
        }

        if (empty($dbResult)) {
            return null;
        }

        return $dbResult;
    }
}
