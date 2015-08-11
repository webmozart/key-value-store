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

use Webmozart\KeyValueStore\Api\KeyValueStore;
use Webmozart\KeyValueStore\Decorator\CountableDecorator;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
class CountableDecoratorTest extends AbstractDecoratorTest
{
    protected function createDecorator(KeyValueStore $innerStore)
    {
        return new CountableDecorator($innerStore);
    }

    public function testCountCache()
    {
        $this->innerStore->expects($this->at(0))
            ->method('keys')
            ->willReturn(array('key1', 'key2'));

        $this->innerStore->expects($this->at(1))
            ->method('set')
            ->with('key3', 'value3');

        $this->innerStore->expects($this->at(2))
            ->method('keys')
            ->willReturn(array('key1', 'key2', 'key3'));

        $this->assertEquals(2, $this->decorator->count());
        $this->assertEquals(2, $this->decorator->count());

        $this->decorator->set('key3', 'value3');

        $this->assertEquals(3, $this->decorator->count());
        $this->assertEquals(3, $this->decorator->count());
    }
}
