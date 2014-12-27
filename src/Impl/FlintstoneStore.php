<?php

/*
 * This file is part of the webmozart/key-value-store package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\KeyValueStore\Impl;

use Exception;
use Flintstone\FlintstoneDB;
use Webmozart\KeyValueStore\Assert\Assertion;
use Webmozart\KeyValueStore\KeyValueStore;
use Webmozart\KeyValueStore\SerializationFailedException;

/**
 * A key-value store backed by a simple file.
 *
 * The {@link FlintstoneDB} class is used to read from and write to the file.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class FlintstoneStore implements KeyValueStore
{
    /**
     * @var FlintstoneDB
     */
    private $db;

    /**
     * Creates a new store using the given database backend.
     *
     * @param FlintstoneDB $db The database to read from and write to.
     */
    public function __construct(FlintstoneDB $db)
    {
        $this->db = $db;
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        Assertion::key($key);

        try {
            $serialized = serialize($value);
        } catch (Exception $e) {
            throw SerializationFailedException::forException($e);
        }

        $this->db->set((string) $key, $serialized);
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        Assertion::key($key);

        if (false === ($serialized = $this->db->get((string) $key))) {
            return $default;
        }

        return unserialize($serialized);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        Assertion::key($key);

        return $this->db->delete((string) $key);
    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        Assertion::key($key);

        return false !== $this->db->get((string) $key);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->db->flush();
    }
}
