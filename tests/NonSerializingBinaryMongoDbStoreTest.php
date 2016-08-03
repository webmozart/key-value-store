<?php

/*
 * This file is part of the vendor/project package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\KeyValueStore\Tests;

use Webmozart\KeyValueStore\MongoDbStore;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <hello@webmozart.io>
 */
class NonSerializingBinaryMongoDbStoreTest extends AbstractMongoDbStoreTest
{
    protected function createStore()
    {
        $collection = $this->client->selectCollection(
            self::DATABASE_NAME,
            self::COLLECTION_NAME
        );

        return new MongoDbStore($collection, MongoDbStore::NO_SERIALIZE | MongoDbStore::SUPPORT_BINARY);
    }

    /**
     * @dataProvider provideScalarValues
     */
    public function testSetSupportsScalarValues($value)
    {
        // JSON cannot handle binary data
        $this->setExpectedException('\Webmozart\KeyValueStore\Api\UnsupportedValueException');

        parent::testSetSupportsScalarValues($value);
    }

    /**
     * @dataProvider provideObjectValues
     */
    public function testSetSupportsObjectValues($value)
    {
        // JSON cannot handle binary data
        $this->setExpectedException('\Webmozart\KeyValueStore\Api\UnsupportedValueException');

        parent::testSetSupportsObjectValues($value);
    }

    /**
     * @dataProvider provideArrayValues
     */
    public function testSetSupportsArrayValues($value)
    {
        // JSON cannot handle binary data
        $this->setExpectedException('\Webmozart\KeyValueStore\Api\UnsupportedValueException');

        parent::testSetSupportsArrayValues($value);
    }

    /**
     * @dataProvider provideBinaryArrayValues
     */
    public function testSetSupportsBinaryArrayValues($value)
    {
        // JSON cannot handle binary data
        $this->setExpectedException('\Webmozart\KeyValueStore\Api\UnsupportedValueException');

        parent::testSetSupportsBinaryArrayValues($value);
    }

    /**
     * @dataProvider provideBinaryObjectValues
     */
    public function testSetSupportsBinaryObjectValues($value)
    {
        // JSON cannot handle binary data
        $this->setExpectedException('\Webmozart\KeyValueStore\Api\UnsupportedValueException');

        parent::testSetSupportsBinaryObjectValues($value);
    }

    /**
     * @dataProvider provideInvalidValues
     */
    public function testSetThrowsExceptionIfValueNotSerializable($value)
    {
        // JSON cannot handle binary data
        $this->setExpectedException('\Webmozart\KeyValueStore\Api\UnsupportedValueException');

        parent::testSetThrowsExceptionIfValueNotSerializable($value);
    }
}
