<?php

/*
 * This file is part of the vendor/project package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\KeyValueStore;

use Closure;
use Exception;
use MongoDB\BSON\Binary;
use MongoDB\Collection;
use MongoDB\Driver\Exception\UnexpectedValueException;
use Webmozart\KeyValueStore\Api\KeyValueStore;
use Webmozart\KeyValueStore\Api\NoSuchKeyException;
use Webmozart\KeyValueStore\Api\ReadException;
use Webmozart\KeyValueStore\Api\UnserializationFailedException;
use Webmozart\KeyValueStore\Api\UnsupportedValueException;
use Webmozart\KeyValueStore\Api\WriteException;
use Webmozart\KeyValueStore\Util\KeyUtil;
use Webmozart\KeyValueStore\Util\Serializer;

/**
 * A key-value-store backed by MongoDB.
 *
 * @since 1.0
 *
 * @author Bernhard Schussek <hello@webmozart.io>
 */
class MongoDbStore implements KeyValueStore
{
    /**
     * Flag: Disable serialization.
     */
    const NO_SERIALIZE = 1;

    /**
     * Flag: Support storage of binary data.
     */
    const SUPPORT_BINARY = 2;

    private static $typeMap = array(
        'root' => 'array',
        'document' => 'array',
        'array' => 'array',
    );

    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var Closure
     */
    private $serialize;

    /**
     * @var Closure
     */
    private $unserialize;

    public function __construct(Collection $collection, $flags = 0)
    {
        $this->collection = $collection;

        if ($flags & self::NO_SERIALIZE) {
            if ($flags & self::SUPPORT_BINARY) {
                $this->serialize = function ($unserialized) {
                    if (!is_string($unserialized)) {
                        throw UnsupportedValueException::forValue($unserialized, $this);
                    }

                    return new Binary($unserialized, Binary::TYPE_GENERIC);
                };
                $this->unserialize = function (Binary $serialized) {
                    return $serialized->getData();
                };
            } else {
                $this->serialize = function ($unserialized) {
                    if (!is_scalar($unserialized) && !is_array($unserialized) && null !== $unserialized) {
                        throw UnsupportedValueException::forValue($unserialized, $this);
                    }

                    return $unserialized;
                };
                $this->unserialize = function ($serialized) {
                    return $serialized;
                };
            }
        } else {
            if ($flags & self::SUPPORT_BINARY) {
                $this->serialize = function ($unserialized) {
                    return new Binary(
                        Serializer::serialize($unserialized),
                        Binary::TYPE_GENERIC
                    );
                };
                $this->unserialize = function (Binary $serialized) {
                    return Serializer::unserialize($serialized->getData());
                };
            } else {
                $this->serialize = function ($unserialized) {
                    return Serializer::serialize($unserialized);
                };
                $this->unserialize = function ($serialized) {
                    return Serializer::unserialize($serialized);
                };
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        KeyUtil::validate($key);

        $serialized = $this->serialize->__invoke($value);

        try {
            $this->collection->replaceOne(
                array('_id' => $key),
                array('_id' => $key, 'value' => $serialized),
                array('upsert' => true)
            );
        } catch (UnexpectedValueException $e) {
            throw UnsupportedValueException::forType('binary', $this, 0, $e);
        } catch (Exception $e) {
            throw WriteException::forException($e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        KeyUtil::validate($key);

        try {
            $document = $this->collection->findOne(
                array('_id' => $key),
                array('typeMap' => self::$typeMap)
            );
        } catch (Exception $e) {
            throw ReadException::forException($e);
        }

        if (null === $document) {
            return $default;
        }

        return $this->unserialize->__invoke($document['value']);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrFail($key)
    {
        KeyUtil::validate($key);

        try {
            $document = $this->collection->findOne(
                array('_id' => $key),
                array('typeMap' => self::$typeMap)
            );
        } catch (Exception $e) {
            throw ReadException::forException($e);
        }

        if (null === $document) {
            throw NoSuchKeyException::forKey($key);
        }

        return $this->unserialize->__invoke($document['value']);
    }

    /**
     * {@inheritdoc}
     */
    public function getMultiple(array $keys, $default = null)
    {
        KeyUtil::validateMultiple($keys);

        $values = array_fill_keys($keys, $default);

        try {
            $cursor = $this->collection->find(
                array('_id' => array('$in' => array_values($keys))),
                array('typeMap' => self::$typeMap)
            );

            foreach ($cursor as $document) {
                $values[$document['_id']] = $this->unserialize->__invoke($document['value']);
            }
        } catch (UnserializationFailedException $e) {
            throw $e;
        } catch (Exception $e) {
            throw ReadException::forException($e);
        }

        return $values;
    }

    /**
     * {@inheritdoc}
     */
    public function getMultipleOrFail(array $keys)
    {
        KeyUtil::validateMultiple($keys);

        $values = array();

        try {
            $cursor = $this->collection->find(
                array('_id' => array('$in' => array_values($keys))),
                array('typeMap' => self::$typeMap)
            );

            foreach ($cursor as $document) {
                $values[$document['_id']] = $this->unserialize->__invoke($document['value']);
            }
        } catch (UnserializationFailedException $e) {
            throw $e;
        } catch (Exception $e) {
            throw ReadException::forException($e);
        }

        $notFoundKeys = array_diff($keys, array_keys($values));

        if (count($notFoundKeys) > 0) {
            throw NoSuchKeyException::forKeys($notFoundKeys);
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
            $result = $this->collection->deleteOne(array('_id' => $key));
            $deletedCount = $result->getDeletedCount();
        } catch (Exception $e) {
            throw WriteException::forException($e);
        }

        return $deletedCount > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function exists($key)
    {
        KeyUtil::validate($key);

        try {
            $count = $this->collection->count(array('_id' => $key));
        } catch (Exception $e) {
            throw ReadException::forException($e);
        }

        return $count > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        try {
            $this->collection->drop();
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
            $cursor = $this->collection->find(array(), array(
                'projection' => array('_id' => 1),
            ));

            $keys = array();

            foreach ($cursor as $document) {
                $keys[] = $document['_id'];
            }
        } catch (Exception $e) {
            throw ReadException::forException($e);
        }

        return $keys;
    }
}
