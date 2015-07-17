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
 * Thrown when a value cannot be serialized.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class SerializationFailedException extends RuntimeException
{
    /**
     * Creates a new exception for the given value.
     *
     * @param mixed     $value  The value that could not be serialized.
     * @param string    $reason The reason why the value could not be
     *                          unserialized.
     * @param int       $code   The exception code.
     * @param Exception $cause  The exception that caused this exception.
     *
     * @return static The new exception.
     */
    public static function forValue($value, $reason = '', $code = 0, Exception $cause = null)
    {
        return self::forType(is_object($value) ? get_class($value) : gettype($value), $reason, $code, $cause);
    }

    /**
     * Creates a new exception for the given value type.
     *
     * @param string    $type   The type that could not be serialized.
     * @param string    $reason The reason why the value could not be
     *                          unserialized.
     * @param int       $code   The exception code.
     * @param Exception $cause  The exception that caused this exception.
     *
     * @return static The new exception.
     */
    public static function forType($type, $reason = '', $code = 0, Exception $cause = null)
    {
        return new static(sprintf(
            'Could not serialize value of type %s%s',
            $type,
            $reason ? ': '.$reason : '.'
        ), $code, $cause);
    }
}
