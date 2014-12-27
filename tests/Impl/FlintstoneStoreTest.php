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

use Flintstone\FlintstoneDB;
use Symfony\Component\Filesystem\Filesystem;
use Webmozart\KeyValueStore\Impl\FlintstoneStore;
use Webmozart\KeyValueStore\Tests\AbstractKeyValueStoreTest;
use Webmozart\KeyValueStore\Tests\PurgeableTestTrait;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class FlintstoneStoreTest extends AbstractKeyValueStoreTest
{
    use PurgeableTestTrait;

    private $tempDir;

    protected function setUp()
    {
        while (false === mkdir($this->tempDir = sys_get_temp_dir().'/webmozart-key-value-store/FlintstoneStoreTest'.rand(10000, 99999), 0777, true)) {}

        parent::setUp();
    }

    protected function tearDown()
    {
        parent::tearDown();

        $filesystem = new Filesystem();
        $filesystem->remove($this->tempDir);
    }

    protected function createStore()
    {
        return new FlintstoneStore(new FlintstoneDB('test', array(
            'dir' => $this->tempDir,
        )));
    }
}
