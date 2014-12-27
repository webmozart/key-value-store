<?php

/*
 * This file is part of the webmozart/key-value-store package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\KeyValueStore\Tests\Impl;

use Webmozart\KeyValueStore\Impl\ArrayStore;
use Webmozart\KeyValueStore\Tests\AbstractKeyValueStoreTest;
use Webmozart\KeyValueStore\Tests\PurgeableTestTrait;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ArrayStoreTest extends AbstractKeyValueStoreTest
{
    use PurgeableTestTrait;

    protected function createStore()
    {
        return new ArrayStore();
    }

    /**
     * @dataProvider provideInvalidValues
     */
    public function testSetSupportsFailsIfValueNotSerializable($value)
    {
        // ArrayStore never serializes its values
        $this->assertTrue(true);
    }
}
