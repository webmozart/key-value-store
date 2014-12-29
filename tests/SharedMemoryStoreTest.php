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

use Webmozart\KeyValueStore\SharedMemoryStore;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class SharedMemoryStoreTest extends AbstractKeyValueStoreTest
{
    private $tempFile;

    protected function setUp()
    {
        $this->tempFile = tempnam(sys_get_temp_dir(), 'SharedMemoryStoreTest');

        parent::setUp();
    }

    protected function tearDown()
    {
        parent::tearDown();

        if (file_exists($this->tempFile)) {
            unlink($this->tempFile);
        }
    }

    protected function createStore()
    {
        return new SharedMemoryStore($this->tempFile);
    }

    public function testCreateFileIfItNotExisting()
    {
        unlink($this->tempFile);

        $store = new SharedMemoryStore($this->tempFile);

        $store->set('foo', 'bar');

        $this->assertSame('bar', $store->get('foo'));
    }
}
