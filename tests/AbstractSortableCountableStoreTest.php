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
abstract class AbstractSortableCountableStoreTest extends AbstractCountableStoreTest
{
    /**
     * @expectedException \Webmozart\KeyValueStore\Api\ReadException
     */
    abstract public function testSortThrowsReadExceptionIfReadFails();

    /**
     * @expectedException \Webmozart\KeyValueStore\Api\WriteException
     */
    abstract public function testSortThrowsWriteExceptionIfWriteFails();

    public function testSortRegularStringKeys()
    {
        $this->store->set('c', 1);
        $this->store->set('a', 2);
        $this->store->set('b', 3);

        $this->store->sort();

        $this->assertSame(array(
            'a' => 2,
            'b' => 3,
            'c' => 1,
        ), $this->store->getMultiple($this->store->keys()));
    }

    public function testSortRegularIntegerKeys()
    {
        $this->store->set(3, 'a');
        $this->store->set(1, 'b');
        $this->store->set(2, 'c');

        $this->store->sort();

        $this->assertSame(array(
            1 => 'b',
            2 => 'c',
            3 => 'a',
        ), $this->store->getMultiple($this->store->keys()));
    }

    public function testSortStringStringKeys()
    {
        $this->store->set('c', 1);
        $this->store->set('a', 2);
        $this->store->set('b', 3);

        $this->store->sort(SORT_STRING);

        $this->assertSame(array(
            'a' => 2,
            'b' => 3,
            'c' => 1,
        ), $this->store->getMultiple($this->store->keys()));
    }

    public function testSortNumericIntegerKeys()
    {
        $this->store->set(3, 'a');
        $this->store->set(1, 'b');
        $this->store->set(2, 'c');

        $this->store->sort(SORT_NUMERIC);

        $this->assertSame(array(
            1 => 'b',
            2 => 'c',
            3 => 'a',
        ), $this->store->getMultiple($this->store->keys()));
    }

    public function testSortNaturalStringKeys()
    {
        if (PHP_VERSION_ID < 50400) {
            $this->markTestSkipped('SORT_NATURAL not available');
        }

        $this->store->set('10', 'c');
        $this->store->set('1', 'g');
        $this->store->set('100', 'a');
        $this->store->set('9', 'b');
        $this->store->set('7a', 'h');
        $this->store->set('7b', 'd');
        $this->store->set('_5', 'z');

        $this->store->sort(SORT_NATURAL);

        $this->assertSame(array(
            '1' => 'g',
            '7a' => 'h',
            '7b' => 'd',
            '9' => 'b',
            '10' => 'c',
            '100' => 'a',
            '_5' => 'z',
        ), $this->store->getMultiple($this->store->keys()));
    }

    public function testSortCaseInsensitiveStringKeys()
    {
        if (PHP_VERSION_ID < 50400) {
            $this->markTestSkipped('SORT_FLAG_CASE not available');
        }

        $this->store->set('_Ac', 'A');
        $this->store->set('abc', 'F');
        $this->store->set('_ab', 'G');
        $this->store->set('ABB', 'E');
        $this->store->set('Bce', 'C');
        $this->store->set('bcd', 'D');
        $this->store->set('bCf', 'E');

        $this->store->sort(SORT_STRING | SORT_FLAG_CASE);

        $this->assertSame(array(
            '_ab' => 'G',
            '_Ac' => 'A',
            'ABB' => 'E',
            'abc' => 'F',
            'bcd' => 'D',
            'Bce' => 'C',
            'bCf' => 'E',
        ), $this->store->getMultiple($this->store->keys()));
    }
}
