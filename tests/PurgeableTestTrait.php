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

use PHPUnit_Framework_Assert;
use Webmozart\KeyValueStore\KeyValueStore;
use Webmozart\KeyValueStore\Purgeable;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
trait PurgeableTestTrait
{
    /**
     * @return KeyValueStore|Purgeable
     */
    abstract protected function getStore();

    public function testPurge()
    {
        $this->getStore()->set('a', 1234);
        $this->getStore()->set('b', 5678);
        $this->getStore()->set('c', 9123);

        $this->getStore()->purge();

        PHPUnit_Framework_Assert::assertFalse($this->getStore()->has('a'));
        PHPUnit_Framework_Assert::assertFalse($this->getStore()->has('b'));
        PHPUnit_Framework_Assert::assertFalse($this->getStore()->has('c'));
    }

    public function testPurgeEmpty()
    {
        $this->getStore()->purge();
    }

    public function testPurgeAndSet()
    {
        $this->getStore()->set('a', 1234);

        $this->getStore()->purge();

        PHPUnit_Framework_Assert::assertFalse($this->getStore()->has('a'));

        $this->getStore()->set('a', 1234);

        PHPUnit_Framework_Assert::assertTrue($this->getStore()->has('a'));
        PHPUnit_Framework_Assert::assertSame(1234, $this->getStore()->get('a'));
    }
}
