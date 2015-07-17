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
 * Thrown when a key is invalid.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class InvalidKeyException extends RuntimeException
{
    /**
     * Creates an exception for an invalid key.
     *
     * @param mixed     $key   The invalid key.
     * @param Exception $cause The exception that caused this exception.
     *
     * @return static The created exception.
     */
    public static function forKey($key, Exception $cause = null)
    {
        return new static(sprintf(
            'Expected a key of type integer or string. Got: %s',
            is_object($key) ? get_class($key) : gettype($key)
        ), 0, $cause);
    }
}
