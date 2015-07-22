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

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
abstract class AbstractCountableStoreTest extends AbstractKeyValueStoreTest
{
    /**
     * @expectedException \Webmozart\KeyValueStore\Api\ReadException
     */
    abstract public function testCountThrowsReadExceptionIfReadFails();

    public function testCountCache()
    {
        $this->assertEquals(0, $this->store->count());

        $this->store->set('foo1', 'bar');
        $this->assertEquals(1, $this->store->count());

        $this->store->set('foo2', 'bar');
        $this->assertEquals(2, $this->store->count());

        $this->store->set('foo3', 'bar');
        $this->assertEquals(3, $this->store->count());

        $this->store->remove('foo2');
        $this->assertEquals(2, $this->store->count());

        $this->store->clear();
        $this->assertEquals(0, $this->store->count());
    }
}
