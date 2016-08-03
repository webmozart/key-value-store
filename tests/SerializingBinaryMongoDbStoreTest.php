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
class SerializingBinaryMongoDbStoreTest extends AbstractMongoDbStoreTest
{
    protected function createStore()
    {
        $collection = $this->client->selectCollection(
            self::DATABASE_NAME,
            self::COLLECTION_NAME
        );

        return new MongoDbStore($collection, MongoDbStore::SUPPORT_BINARY);
    }
}
