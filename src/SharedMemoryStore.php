<?php

/*
 * This file is part of the webmozart/key-value-store package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\KeyValueStore;

use Exception;
use Webmozart\KeyValueStore\Api\KeyValueStore;
use Webmozart\KeyValueStore\Api\SerializationFailedException;
use Webmozart\KeyValueStore\Api\WriteException;
use Webmozart\KeyValueStore\Assert\Assert;

/**
 * A key-value store backed by a shared memory.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class SharedMemoryStore implements KeyValueStore
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var resource
     */
    private $resource;

    /**
     * @var int
     */
    private $size;

    /**
     * @var int
     */
    private $permissions;

    /**
     * Creates a store using the shared memory at the given path.
     *
     * @param string   $path        The path to the shared memory.
     * @param int|null $size        The size of the memory, if it needs to be
     *                              created. Defaults to the value of the
     *                              "sysvshm.init_mem" setting in php.ini or
     *                              to 10000 bytes if not set.
     * @param int      $permissions The permissions for the shared memory, if
     *                              it needs to be created.
     */
    public function __construct($path, $size = null, $permissions = 0666)
    {
        $this->path = $path;
        // see http://php.net/manual/en/function.shm-attach.php
        $this->size = $size ?: (ini_get('sysvshm.init_mem') ?: 10000);
        $this->permissions = $permissions;
    }

    /**
     * Detaches from the shared memory.
     */
    public function __destruct()
    {
        if ($this->resource) {
            $this->disconnect();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        Assert::key($key);

        if (!$this->resource) {
            $this->connect();
        }

        if (is_resource($value)) {
            throw SerializationFailedException::forValue($value);
        }

        try {
            shm_put_var($this->resource, $this->keyToInt($key), $value);
        } catch (Exception $e) {
            throw SerializationFailedException::forValue($value, $e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        Assert::key($key);

        if (!$this->resource) {
            $this->connect();
        }

        $intKey = $this->keyToInt($key);

        return shm_has_var($this->resource, $intKey)
            ? shm_get_var($this->resource, $intKey)
            : $default;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        Assert::key($key);

        if (!$this->resource) {
            $this->connect();
        }

        return @shm_remove_var($this->resource, $this->keyToInt($key));
    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        Assert::key($key);

        if (!$this->resource) {
            $this->connect();
        }

        return shm_has_var($this->resource, $this->keyToInt($key));
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        if (!$this->resource) {
            $this->connect();
        }

        if (!shm_remove($this->resource)) {
            return;
        }

        $this->disconnect();
    }

    private function connect()
    {
        if (!file_exists($this->path)) {
            touch($this->path);
        }

        if (!($resource = shm_attach(ftok($this->path, 'a'), $this->size, $this->permissions))) {
            throw new WriteException('Could not create the shared memory segment.');
        }

        $this->resource = $resource;
    }

    private function disconnect()
    {
        shm_detach($this->resource);

        $this->resource = null;
    }

    private function keyToInt($key)
    {
        // Idea borrowed from the class ShmProxy in
        // https://github.com/adammbalogh/key-value-store-shared-memory
        return (int) sprintf("%u\n", crc32($key));
    }
}
