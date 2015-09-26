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

use Webmozart\KeyValueStore\SerializingArrayStore;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class SerializingArrayStoreTest extends ArrayStoreTest
{
    protected function createStore()
    {
        return new SerializingArrayStore();
    }

    protected function createPopulatedStore(array $values)
    {
        return new SerializingArrayStore($values);
    }
}
