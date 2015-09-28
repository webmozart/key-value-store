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
use Webmozart\Assert\Assert;
use Webmozart\KeyValueStore\Util\KeyUtil;
use Webmozart\KeyValueStore\Api\KeyValueStore;
use Webmozart\KeyValueStore\Api\NoSuchKeyException;
use Webmozart\KeyValueStore\Api\ReadException;
use Webmozart\KeyValueStore\Api\WriteException;
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
    private $table;

    /**
     * @param Connection $connection A doctrine connection instance
     * @param string     $table      The name of the database table
     */
    public function __construct(Connection $connection, $table = 'store')
    {
        Assert::string($table, 'The table must be a string. Got: %s');
        Assert::notEmpty($table, 'The table must not be empty.');

        $this->connection = $connection;
        $this->table = $table;
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        KeyUtil::validate($key);

        try {
            $existing = $this->exists($key);
        } catch (\Exception $e) {
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

        try {
            $result = $this->connection->fetchColumn('SELECT value FROM ' . $this->table . ' WHERE key = ?', array($key), 0);
        } catch (\Exception $e) {
            throw ReadException::forException($e);
        }

        if (false === $result) {
            return $default;
        }

        return Serializer::unserialize($result);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrFail($key)
    {
        KeyUtil::validate($key);

        $result = $this->get($key, null);

        if (null === $result) {
            throw NoSuchKeyException::forKey($key);
        }

        return $result;
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

        $values = array();
        $resolved = array();
        foreach ($data as $row) {
            $values[$row['key']] = Serializer::unserialize($row['value']);
            $resolved[$row['key']] = $row['key'];
        }

        $notResolvedArr = array_diff($keys, $resolved);
        foreach ($notResolvedArr as $notResolved) {
            $values[$notResolved] = $default;
        }

        return $values;
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

        $values = array();
        $resolved = array();
        foreach ($data as $row) {
            $values[$row['key']] = Serializer::unserialize($row['value']);
            $resolved[] = $row['key'];
        }

        $notResolvedArr = array_diff($keys, $resolved);

        if (!empty($notResolvedArr)) {
            throw NoSuchKeyException::forKeys($notResolvedArr);
        }

        return $values;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        KeyUtil::validate($key);

        try {
            $result = $this->connection->delete($this->table, array('key' => $key));
        } catch (\Exception $e) {
            throw WriteException::forException($e);
        }

        if ($result === 1) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function exists($key)
    {
        KeyUtil::validate($key);

        try {
            $result = $this->connection->fetchAssoc('select * from ' . $this->table . ' WHERE key = ?', array($key));
        } catch (\Exception $e) {
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
            $connection = $this->connection->query('DELETE FROM ' . $this->table);
            $connection->execute();
        } catch (\Exception $e) {
            throw WriteException::forException($e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function keys()
    {
        try {
            $stmt = $this->connection->query('SELECT key FROM ' . $this->table);
            $result = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        } catch (\Exception $e) {
            throw ReadException::forException($e);
        }

        return $result;
    }

    private function doInsert($key, $value)
    {
        $serialized = Serializer::serialize($value);

        try {
            $this->connection->insert($this->table, array(
                'key' => $key,
                'value' => $serialized
            ));
        } catch (\Exception $e) {
            throw WriteException::forException($e);
        }
    }

    private function doUpdate($key, $value)
    {
        $serialized = Serializer::serialize($value);

        try {
            $this->connection->update($this->table, array(
                'value' => $serialized
            ), array(
                'key' => $key
            ));
        } catch (\Exception $e) {
            throw WriteException::forException($e);
        }
    }

    /**
     * @param Schema $schema
     *
     * @return \Doctrine\DBAL\Schema\Table|null
     */
    public function configureSchema(Schema $schema)
    {
        if ($schema->hasTable($this->table)) {
            return null;
        }

        return $this->configureTable();
    }

    public function configureTable()
    {
        $schema = new Schema();

        $table = $schema->createTable($this->table);

        $table->addColumn('id', 'integer', array('autoincrement' => true));
        $table->addColumn('key', 'string', array('length' => 255));
        $table->addColumn('value', 'object');
        $table->setPrimaryKey(array('id'));
        $table->addUniqueIndex(['key']);

        return $table;
    }

    private function doGetMultiple(array $keys)
    {
        try {
            $stmt = $this->connection->executeQuery('SELECT * FROM ' . $this->table . ' WHERE key IN (?)',
                array($keys),
                array(Connection::PARAM_STR_ARRAY)
            );
            $data = $stmt->fetchAll();
        } catch (\Exception $e) {
            throw ReadException::forException($e);
        }

        return $data;
    }
}