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

use Webmozart\KeyValueStore\ArrayStore;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ArrayStoreTest extends AbstractKeyValueStoreTest
{
    protected function createStore()
    {
        return new ArrayStore();
    }

    /**
     * @dataProvider provideInvalidValues
     */
    public function testSetThrowsExceptionIfValueNotSerializable($value)
    {
        // ArrayStore never serializes its values
        $this->assertTrue(true);
    }

    public function testSetThrowsWriteExceptionIfWriteFails()
    {
        // ArrayStore never writes a storage
        $this->assertTrue(true);
    }

    public function testRemoveThrowsWriteExceptionIfWriteFails()
    {
        // ArrayStore never writes a storage
        $this->assertTrue(true);
    }

    public function testClearThrowsWriteExceptionIfWriteFails()
    {
        // ArrayStore never writes a storage
        $this->assertTrue(true);
    }

    public function testGetThrowsReadExceptionIfReadFails()
    {
        // ArrayStore never reads a storage
        $this->assertTrue(true);
    }

    public function testGetThrowsExceptionIfNotUnserializable()
    {
        // ArrayStore never serializes its values
        $this->assertTrue(true);
    }

    public function testGetOrFailThrowsReadExceptionIfReadFails()
    {
        // ArrayStore never reads a storage
        $this->assertTrue(true);
    }

    public function testGetOrFailThrowsExceptionIfNotUnserializable()
    {
        // ArrayStore never serializes its values
        $this->assertTrue(true);
    }

    public function testGetMultipleThrowsReadExceptionIfReadFails()
    {
        // ArrayStore never reads a storage
        $this->assertTrue(true);
    }

    public function testGetMultipleThrowsExceptionIfNotUnserializable()
    {
        // ArrayStore never serializes its values
        $this->assertTrue(true);
    }

    public function testGetMultipleOrFailThrowsReadExceptionIfReadFails()
    {
        // ArrayStore never reads a storage
        $this->assertTrue(true);
    }

    public function testGetMultipleOrFailThrowsExceptionIfNotUnserializable()
    {
        // ArrayStore never serializes its values
        $this->assertTrue(true);
    }

    public function testExistsThrowsReadExceptionIfReadFails()
    {
        // ArrayStore never reads a storage
        $this->assertTrue(true);
    }

    public function testKeysThrowsReadExceptionIfReadFails()
    {
        // ArrayStore never reads a storage
        $this->assertTrue(true);
    }
}
