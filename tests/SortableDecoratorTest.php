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

use Webmozart\KeyValueStore\Api\KeyValueStore;
use Webmozart\KeyValueStore\ArrayStore;
use Webmozart\KeyValueStore\SortableDecorator;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
class SortableDecoratorTest extends AbstractDecoratorTest
{
    protected function createDecorator(KeyValueStore $innerStore)
    {
        return new SortableDecorator($innerStore);
    }

    public function testSortRegularStringKeys()
    {
        $store = new SortableDecorator(new ArrayStore());
        $store->set('c', 1);
        $store->set('a', 2);
        $store->set('b', 3);

        $store->sort();

        $this->assertSame(array(
            'a' => 2,
            'b' => 3,
            'c' => 1,
        ), $store->getMultiple($store->keys()));
    }

    public function testSortRegularIntegerKeys()
    {
        $store = new SortableDecorator(new ArrayStore());
        $store->set(3, 'a');
        $store->set(1, 'b');
        $store->set(2, 'c');

        $store->sort();

        $this->assertSame(array(
            1 => 'b',
            2 => 'c',
            3 => 'a',
        ), $store->getMultiple($store->keys()));
    }

    public function testSortStringStringKeys()
    {
        $store = new SortableDecorator(new ArrayStore());
        $store->set('c', 1);
        $store->set('a', 2);
        $store->set('b', 3);

        $store->sort(SORT_STRING);

        $this->assertSame(array(
            'a' => 2,
            'b' => 3,
            'c' => 1,
        ), $store->getMultiple($store->keys()));
    }

    public function testSortNumericIntegerKeys()
    {
        $store = new SortableDecorator(new ArrayStore());
        $store->set(3, 'a');
        $store->set(1, 'b');
        $store->set(2, 'c');

        $store->sort(SORT_NUMERIC);

        $this->assertSame(array(
            1 => 'b',
            2 => 'c',
            3 => 'a',
        ), $store->getMultiple($store->keys()));
    }

    public function testSortNaturalStringKeys()
    {
        if (PHP_VERSION_ID < 50400) {
            $this->markTestSkipped('SORT_NATURAL not available');
        }

        $store = new SortableDecorator(new ArrayStore());

        $store->set('10', 'c');
        $store->set('1', 'g');
        $store->set('100', 'a');
        $store->set('9', 'b');
        $store->set('7a', 'h');
        $store->set('7b', 'd');
        $store->set('_5', 'z');

        $store->sort(SORT_NATURAL);

        $this->assertSame(array(
            '1' => 'g',
            '7a' => 'h',
            '7b' => 'd',
            '9' => 'b',
            '10' => 'c',
            '100' => 'a',
            '_5' => 'z',
        ), $store->getMultiple($store->keys()));
    }

    public function testSortCaseInsensitiveStringKeys()
    {
        if (PHP_VERSION_ID < 50400) {
            $this->markTestSkipped('SORT_FLAG_CASE not available');
        }

        $store = new SortableDecorator(new ArrayStore());

        $store->set('_Ac', 'A');
        $store->set('abc', 'F');
        $store->set('_ab', 'G');
        $store->set('ABB', 'E');
        $store->set('Bce', 'C');
        $store->set('bcd', 'D');
        $store->set('bCf', 'E');

        $store->sort(SORT_STRING | SORT_FLAG_CASE);

        $this->assertSame(array(
            '_ab' => 'G',
            '_Ac' => 'A',
            'ABB' => 'E',
            'abc' => 'F',
            'bcd' => 'D',
            'Bce' => 'C',
            'bCf' => 'E',
        ), $store->getMultiple($store->keys()));
    }
}
