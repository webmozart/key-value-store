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

/**
 * A key-value store.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface KeyValueStore
{
    /**
     * Sets the value for a key in the store.
     *
     * @param int|string $key   The key to set.
     * @param mixed      $value The value to set for the key.
     *
     * @throws InvalidKeyException If the key is invalid.
     * @throws SerializationFailedException If the value cannot be serialized.
     * @throws StorageException If the storage of the key failed.
     */
    public function set($key, $value);

    /**
     * Returns the value of a key in the store.
     *
     * @param int|string $key     The key to get.
     * @param mixed      $default The value to return if the key is not set.
     *
     * @return mixed The value of the key or the default value if the key is
     *               not set.
     *
     * @throws InvalidKeyException If the key is invalid.
     * @throws StorageException If the retrieval of the key failed.
     */
    public function get($key, $default = null);

    /**
     * Removes a key from the store.
     *
     * If the store does not contain the key, this method does nothing.
     *
     * @param int|string $key The key to remove.
     *
     * @return bool Whether a key was removed.
     *
     * @throws InvalidKeyException If the key is invalid.
     * @throws StorageException If the removal of the key failed.
     */
    public function remove($key);

    /**
     * Returns whether the store contains a key.
     *
     * @param int|string $key The key to test.
     *
     * @return bool Whether the store contains the key.
     *
     * @throws InvalidKeyException If the key is invalid.
     * @throws StorageException If the query of the key failed.
     */
    public function has($key);

    /**
     * Removes all keys from the store.
     *
     * @throws StorageException If the removal failed.
     */
    public function clear();
}
