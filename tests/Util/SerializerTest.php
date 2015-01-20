<?php

/*
 * This file is part of the webmozart/key-value-store package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\KeyValueStore\Tests\Util;

use PHPUnit_Framework_TestCase;
use Webmozart\KeyValueStore\Api\SerializationFailedException;
use Webmozart\KeyValueStore\Api\UnserializationFailedException;
use Webmozart\KeyValueStore\Tests\Fixtures\NotSerializable;
use Webmozart\KeyValueStore\Util\Serializer;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class SerializerTest extends PHPUnit_Framework_TestCase
{
    public function testSerialize()
    {
        $data = (object) array('foo' => 'bar');

        $this->assertEquals($data, Serializer::unserialize(Serializer::serialize($data)));
    }

    public function testSerializeFailsIfResource()
    {
        $data = fopen(__FILE__, 'r');

        try {
            Serializer::serialize($data);
            $this->fail('Expected a SerializationFailedException');
        } catch (SerializationFailedException $e) {
        }

        $this->assertTrue(true, 'Exception caught');
    }

    public function testSerializeFailsIfNotSerializable()
    {
        $data = new NotSerializable();

        try {
            Serializer::serialize($data);
            $this->fail('Expected a SerializationFailedException');
        } catch (SerializationFailedException $e) {
        }

        $this->assertTrue(true, 'Exception caught');
    }

    public function testUnserializeFailsIfInvalidString()
    {
        try {
            Serializer::unserialize('foobar');
            $this->fail('Expected an UnserializationFailedException');
        } catch (UnserializationFailedException $e) {
            $this->assertContains('Error at offset 0', $e->getMessage());
        }
    }

    public function testUnserializeFailsIfNoString()
    {
        try {
            Serializer::unserialize(1234);
            $this->fail('Expected an UnserializationFailedException');
        } catch (UnserializationFailedException $e) {
            $this->assertContains('Could not unserialize value of type integer.', $e->getMessage());
        }
    }
}
