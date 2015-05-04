<?php

/*
 * This file is part of the webmozart/key-value-store package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\KeyValueStore\Api;

/**
 * A key-value store.
 *
 * KeyUtil-value stores support storing values for integer or string keys chosen
 * by the user. Any serializable value can be stored, although an implementation
 * of this interface may further restrict the range of accepted values. See the
 * documentation of the implementation for more information.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface KeyValueStore
{
    /**
     * Sets the value for a key in the store.
     *
     * The key-value store accepts any serializable value. If a value is not
     * serializable, a {@link SerializationFailedException} is thrown.
     * Additionally, implementations may put further restrictions on their
     * accepted values. If an unsupported value is passed, an
     * {@link UnsupportedValueException} is thrown. Check the documentation of
     * the implementation to learn more about its supported values.
     *
     * Any integer or string value is accepted as key. If any other type is
     * passed for the key, an {@link InvalidKeyException} is thrown. You should
     * make sure that you only pass valid keys to the store.
     *
     * If the backend of the store cannot be written, a {@link WriteException}
     * is thrown. You should always handle this exception in your code:
     *
     * ```php
     * try {
     *     $store->set($key, $value);
     * } catch (WriteException $e) {
     *     // write failed
     * }
     * ```
     *
     * @param int|string $key   The key to set.
     * @param mixed      $value The value to set for the key.
     *
     * @throws WriteException If the store cannot be written.
     * @throws InvalidKeyException If the key is not a string or integer.
     * @throws SerializationFailedException If the value cannot be serialized.
     * @throws UnsupportedValueException If the value is not supported by the
     *                                   implementation.
     */
    public function set($key, $value);

    /**
     * Returns the value of a key in the store.
     *
     * If a key does not exist in the store, the default value passed in the
     * second parameter is returned.
     *
     * Any integer or string value is accepted as key. If any other type is
     * passed for the key, an {@link InvalidKeyException} is thrown. You should
     * make sure that you only pass valid keys to the store.
     *
     * If the backend of the store cannot be read, a {@link ReadException}
     * is thrown. You should always handle this exception in your code:
     *
     * ```php
     * try {
     *     $value = $store->get($key);
     * } catch (ReadException $e) {
     *     // read failed
     * }
     * ```
     *
     * @param int|string $key     The key to get.
     * @param mixed      $default The default value to return if the key does
     *                            not exist.
     *
     * @return mixed The value of the key or the default value if the key does
     *               not exist.
     *
     * @throws ReadException If the store cannot be read.
     * @throws InvalidKeyException If the key is not a string or integer.
     * @throws UnserializationFailedException If the stored value cannot be
     *                                        unserialized.
     */
    public function get($key, $default = null);

    /**
     * Returns the value of a key in the store.
     *
     * If the key does not exist in the store, an exception is thrown.
     *
     * Any integer or string value is accepted as key. If any other type is
     * passed for the key, an {@link InvalidKeyException} is thrown. You should
     * make sure that you only pass valid keys to the store.
     *
     * If the backend of the store cannot be read, a {@link ReadException}
     * is thrown. You should always handle this exception in your code:
     *
     * ```php
     * try {
     *     $value = $store->getOrFail($key);
     * } catch (ReadException $e) {
     *     // read failed
     * }
     * ```
     *
     * @param int|string $key The key to get.
     *
     * @return mixed The value of the key.
     *
     * @throws ReadException If the store cannot be read.
     * @throws NoSuchKeyException If the key was not found.
     * @throws InvalidKeyException If the key is not a string or integer.
     * @throws UnserializationFailedException If the stored value cannot be
     *                                        unserialized.
     */
    public function getOrFail($key);

    /**
     * Returns the values of multiple keys in the store.
     *
     * The passed default value is returned for keys that don't exist.
     *
     * Any integer or string value is accepted as key. If any other type is
     * passed for the key, an {@link InvalidKeyException} is thrown. You should
     * make sure that you only pass valid keys to the store.
     *
     * If the backend of the store cannot be read, a {@link ReadException}
     * is thrown. You should always handle this exception in your code:
     *
     * ```php
     * try {
     *     $value = $store->getMultiple(array($key1, $key2));
     * } catch (ReadException $e) {
     *     // read failed
     * }
     * ```
     *
     * @param array $keys    The keys to get. The keys must be strings or integers.
     * @param mixed $default The default value to return for keys that are not
     *                       found.
     *
     * @return array The values of the passed keys, indexed by the keys.
     *
     * @throws ReadException If the store cannot be read.
     * @throws NoSuchKeyException If a key was not found.
     * @throws InvalidKeyException If a key is not a string or integer.
     * @throws UnserializationFailedException If a stored value cannot be
     *                                        unserialized.
     */
    public function getMultiple(array $keys, $default = null);

    /**
     * Returns the values of multiple keys in the store.
     *
     * If a key does not exist in the store, an exception is thrown.
     *
     * Any integer or string value is accepted as key. If any other type is
     * passed for the key, an {@link InvalidKeyException} is thrown. You should
     * make sure that you only pass valid keys to the store.
     *
     * If the backend of the store cannot be read, a {@link ReadException}
     * is thrown. You should always handle this exception in your code:
     *
     * ```php
     * try {
     *     $value = $store->getMultipleOrFail(array($key1, $key2));
     * } catch (ReadException $e) {
     *     // read failed
     * }
     * ```
     *
     * @param array $keys The keys to get. The keys must be strings or integers.
     *
     * @return array The values of the passed keys, indexed by the keys.
     *
     * @throws ReadException If the store cannot be read.
     * @throws NoSuchKeyException If a key was not found.
     * @throws InvalidKeyException If a key is not a string or integer.
     * @throws UnserializationFailedException If a stored value cannot be
     *                                        unserialized.
     */
    public function getMultipleOrFail(array $keys);

    /**
     * Removes a key from the store.
     *
     * If the store does not contain the key, this method returns `false`.
     *
     * Any integer or string value is accepted as key. If any other type is
     * passed for the key, an {@link InvalidKeyException} is thrown. You should
     * make sure that you only pass valid keys to the store.
     *
     * If the backend of the store cannot be written, a {@link WriteException}
     * is thrown. You should always handle this exception in your code:
     *
     * ```php
     * try {
     *     $store->remove($key);
     * } catch (WriteException $e) {
     *     // write failed
     * }
     * ```
     *
     * @param int|string $key The key to remove.
     *
     * @return bool Returns `true` if a key was removed from the store.
     *
     * @throws WriteException If the store cannot be written.
     * @throws InvalidKeyException If the key is not a string or integer.
     */
    public function remove($key);

    /**
     * Returns whether a key exists.
     *
     * Any integer or string value is accepted as key. If any other type is
     * passed for the key, an {@link InvalidKeyException} is thrown. You should
     * make sure that you only pass valid keys to the store.
     *
     * If the backend of the store cannot be read, a {@link ReadException}
     * is thrown. You should always handle this exception in your code:
     *
     * ```php
     * try {
     *     if ($store->exists($key)) {
     *         // ...
     *     }
     * } catch (ReadException $e) {
     *     // read failed
     * }
     * ```
     *
     * @param int|string $key The key to test.
     *
     * @return bool Whether the key exists in the store.
     *
     * @throws ReadException If the store cannot be read.
     * @throws InvalidKeyException If the key is not a string or integer.
     */
    public function exists($key);

    /**
     * Removes all keys from the store.
     *
     * If the backend of the store cannot be written, a {@link WriteException}
     * is thrown. You should always handle this exception in your code:
     *
     * ```php
     * try {
     *     $store->clear();
     * } catch (WriteException $e) {
     *     // write failed
     * }
     * ```
     *
     * @throws WriteException If the store cannot be written.
     */
    public function clear();

    /**
     * Returns all keys currently stored in the store.
     *
     * If the backend of the store cannot be read, a {@link ReadException}
     * is thrown. You should always handle this exception in your code:
     *
     * ```php
     * try {
     *     foreach ($store->keys() as $key) {
     *         // ...
     *     }
     * } catch (ReadException $e) {
     *     // read failed
     * }
     * ```
     *
     * @return array The keys stored in the store. Each key is either a string
     *               or an integer. The order of the keys is undefined.
     *
     * @throws ReadException If the store cannot be read.
     */
    public function keys();
}
