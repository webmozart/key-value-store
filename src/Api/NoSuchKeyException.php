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

use Exception;
use RuntimeException;

/**
 * Thrown when a key was not found in the store.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class NoSuchKeyException extends RuntimeException
{
    /**
     * Creates an exception for a key that was not found.
     *
     * @param string|int     $key   The key that was not found.
     * @param Exception|null $cause The exception that caused this exception.
     *
     * @return static The created exception.
     */
    public static function forKey($key, Exception $cause = null)
    {
        return new static(sprintf(
            'The key "%s" does not exist.',
            $key
        ), 0, $cause);
    }

    /**
     * Creates an exception for multiple keys that were not found.
     *
     * @param array[]        $keys  The keys that were not found.
     * @param Exception|null $cause The exception that caused this exception.
     *
     * @return static The created exception.
     */
    public static function forKeys(array $keys, Exception $cause = null)
    {
        return new static(sprintf(
            'The keys "%s" does not exist.',
            implode('", "', $keys)
        ), 0, $cause);
    }
}
