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

use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use Webmozart\KeyValueStore\Api\KeyValueStore;
use Webmozart\KeyValueStore\ArrayStore;
use Webmozart\KeyValueStore\SortableDecorator;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
class SortableDecoratorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject|KeyValueStore
     */
    private $innerStore;

    /**
     * @var SortableDecorator
     */
    private $store;

    protected function setUp()
    {
        $this->innerStore = $this->getMock('Webmozart\KeyValueStore\Api\KeyValueStore');
        $this->store = new SortableDecorator($this->innerStore);
    }

    public function testGetDelegate()
    {
        $this->innerStore->expects($this->once())
            ->method('get')
            ->with('key');

        $this->store->get('key');
    }

    public function testGetOrFailDelegate()
    {
        $this->innerStore->expects($this->once())
            ->method('getOrFail')
            ->with('key');

        $this->store->getOrFail('key');
    }

    public function testGetMultipleDelegate()
    {
        $this->innerStore->expects($this->once())
            ->method('getMultiple')
            ->with(array('key1', 'key2'));

        $this->store->getMultiple(array('key1', 'key2'));
    }

    public function testGetMultipleOrFailDelegate()
    {
        $this->innerStore->expects($this->once())
            ->method('getMultipleOrFail')
            ->with(array('key1', 'key2'));

        $this->store->getMultipleOrFail(array('key1', 'key2'));
    }

    public function testSetDelegate()
    {
        $this->innerStore->expects($this->once())
            ->method('set')
            ->with('key', 'value');

        $this->store->set('key', 'value');
    }

    public function testRemoveDelegate()
    {
        $this->innerStore->expects($this->once())
            ->method('remove')
            ->with('key');

        $this->store->remove('key');
    }

    public function testExistsDelegate()
    {
        $this->innerStore->expects($this->once())
            ->method('exists')
            ->with('key');

        $this->store->exists('key');
    }

    public function testClearDelegate()
    {
        $this->innerStore->expects($this->once())
            ->method('clear');

        $this->store->clear();
    }

    public function testSortRegularStringKeys()
    {
        $this->store = new SortableDecorator(new ArrayStore());
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
        $this->store = new SortableDecorator(new ArrayStore());
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
        $this->store = new SortableDecorator(new ArrayStore());
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
        $this->store = new SortableDecorator(new ArrayStore());
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

        $this->store = new SortableDecorator(new ArrayStore());

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

        $this->store = new SortableDecorator(new ArrayStore());

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
