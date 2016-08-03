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
class SerializingMongoDbStoreTest extends AbstractMongoDbStoreTest
{
    protected function createStore()
    {
        $collection = $this->client->selectCollection(
            self::DATABASE_NAME,
            self::COLLECTION_NAME
        );

        return new MongoDbStore($collection);
    }

    /**
     * @dataProvider provideBinaryValues
     */
    public function testSetSupportsBinaryValues($value)
    {
        // JSON cannot handle binary data
        $this->setExpectedException('\Webmozart\KeyValueStore\Api\UnsupportedValueException');

        parent::testSetSupportsBinaryValues($value);
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
}
