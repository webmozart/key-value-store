<?php

/*
 * This file is part of the webmozart/key-value-store package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\KeyValueStore\Tests\Decorator;

use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use Webmozart\KeyValueStore\Api\KeyValueStore;
use Webmozart\KeyValueStore\Decorator\AbstractDecorator;
use Webmozart\KeyValueStore\Decorator\SortableDecorator;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
abstract class AbstractDecoratorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject|KeyValueStore
     */
    protected $innerStore;

    /**
     * @var SortableDecorator
     */
    protected $decorator;

    /**
     * @param KeyValueStore $innerStore
     *
     * @return KeyValueStore|AbstractDecorator The created store.
     */
    abstract protected function createDecorator(KeyValueStore $innerStore);

    protected function setUp()
    {
        $this->innerStore = $this->getMock('Webmozart\KeyValueStore\Api\KeyValueStore');
        $this->decorator = $this->createDecorator($this->innerStore);
    }

    protected function tearDown()
    {
        if ($this->decorator) {
            $this->decorator->clear();
        }
    }

    public function testGetDelegate()
    {
        $this->innerStore->expects($this->once())
            ->method('get')
            ->with('key');

        $this->decorator->get('key');
    }

    public function testGetOrFailDelegate()
    {
        $this->innerStore->expects($this->once())
            ->method('getOrFail')
            ->with('key');

        $this->decorator->getOrFail('key');
    }

    public function testGetMultipleDelegate()
    {
        $this->innerStore->expects($this->once())
            ->method('getMultiple')
            ->with(array('key1', 'key2'));

        $this->decorator->getMultiple(array('key1', 'key2'));
    }

    public function testGetMultipleOrFailDelegate()
    {
        $this->innerStore->expects($this->once())
            ->method('getMultipleOrFail')
            ->with(array('key1', 'key2'));

        $this->decorator->getMultipleOrFail(array('key1', 'key2'));
    }

    public function testSetDelegate()
    {
        $this->innerStore->expects($this->once())
            ->method('set')
            ->with('key', 'value');

        $this->decorator->set('key', 'value');
    }

    public function testRemoveDelegate()
    {
        $this->innerStore->expects($this->once())
            ->method('remove')
            ->with('key');

        $this->decorator->remove('key');
    }

    public function testExistsDelegate()
    {
        $this->innerStore->expects($this->once())
            ->method('exists')
            ->with('key');

        $this->decorator->exists('key');
    }

    public function testClearDelegate()
    {
        $this->innerStore->expects($this->once())
            ->method('clear');

        $this->decorator->clear();
    }

    public function testKeysDelegate()
    {
        $this->innerStore->expects($this->once())
            ->method('keys');

        $this->decorator->keys();
    }
}
