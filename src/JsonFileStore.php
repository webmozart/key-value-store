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
use stdClass;
use Webmozart\KeyValueStore\Api\KeyValueStore;
use Webmozart\KeyValueStore\Api\SerializationFailedException;
use Webmozart\KeyValueStore\Assert\Assert;

/**
 * A key-value store backed by a JSON file.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class JsonFileStore implements KeyValueStore
{
    private $path;

    private $cacheStore;

    public function __construct($path, $cache = true)
    {
        Assert::string($path, 'The path must be a string. Got: %s');
        Assert::notEmpty($path, 'The path must not be empty.');
        Assert::boolean($cache, 'The cache argument must be a boolean. Got: %s');

        $this->path = $path;

        if ($cache) {
            $this->cacheStore = new ArrayStore();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        Assert::key($key);

        if ($this->cacheStore) {
            $this->cacheStore->set($key, $value);
        }

        $data = $this->load();

        if (is_object($value) || is_string($value)) {
            try {
                $value = serialize($value);
            } catch (Exception $e) {
                throw SerializationFailedException::forException($e);
            }
        }

        $data->$key = $value;

        $this->save($data);
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        Assert::key($key);

        if ($this->cacheStore && $this->cacheStore->has($key)) {
            return $this->cacheStore->get($key);
        }

        $data = $this->load();

        if (!property_exists($data, $key)) {
            return $default;
        }

        $value = $data->$key;

        if (is_string($value)) {
            $value = unserialize($value);
        }

        if ($this->cacheStore) {
            $this->cacheStore->set($key, $value);
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        Assert::key($key);

        if ($this->cacheStore) {
            $this->cacheStore->remove($key);
        }

        $data = $this->load();

        if (!property_exists($data, $key)) {
            return false;
        }

        unset($data->$key);

        $this->save($data);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        Assert::key($key);

        if ($this->cacheStore && $this->cacheStore->has($key)) {
            return true;
        }

        $data = $this->load();

        return property_exists($data, $key);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->save(new stdClass());

        if ($this->cacheStore) {
            $this->cacheStore->clear();
        }
    }

    private function load()
    {
        $contents = file_exists($this->path)
            ? trim(file_get_contents($this->path))
            : null;

        return $contents ? json_decode($contents) : new stdClass();
    }

    private function save($data)
    {
        return file_put_contents($this->path, json_encode($data));
    }
}
