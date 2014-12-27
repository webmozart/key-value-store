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
use RuntimeException;

/**
 * Thrown when a value is invalid.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class SerializationFailedException extends RuntimeException
{
    /**
     * Creates a new exception for the given exception.
     *
     * @param Exception $e The exception that occurred.
     *
     * @return static The new exception.
     */
    public static function forException(Exception $e)
    {
        return new static('Could not serialize value: '.$e->getMessage(), $e->getCode(), $e);
    }
}
