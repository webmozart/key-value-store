<?php

/*
 * This file is part of the webmozart/key-value-store package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\KeyValueStore\Util;

use Exception;
use Webmozart\KeyValueStore\Api\SerializationFailedException;
use Webmozart\KeyValueStore\Api\UnserializationFailedException;

/**
 * Wrapper for `serialize()`/`unserialize()` that throws proper exceptions.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Serializer
{
    /**
     * Serializes a value.
     *
     * @param mixed $value The value to serialize.
     *
     * @return string The serialized value.
     *
     * @throws SerializationFailedException If the value cannot be serialized.
     */
    public static function serialize($value)
    {
        if (is_resource($value)) {
            throw SerializationFailedException::forValue($value);
        }

        try {
            $serialized = serialize($value);
        } catch (Exception $e) {
            throw SerializationFailedException::forValue($value, $e->getMessage(), $e->getCode(), $e);
        }

        return $serialized;
    }

    /**
     * Unserializes a value.
     *
     * @param mixed $serialized The serialized value.
     *
     * @return string The unserialized value.
     *
     * @throws UnserializationFailedException If the value cannot be unserialized.
     */
    public static function unserialize($serialized)
    {
        if (!is_string($serialized)) {
            throw UnserializationFailedException::forValue($serialized);
        }

        $errorMessage = null;
        $errorCode = 0;

        set_error_handler(function ($errno, $errstr) use (&$errorMessage, &$errorCode) {
            $errorMessage = $errstr;
            $errorCode = $errno;
        });

        $value = unserialize($serialized);

        restore_error_handler();

        if (null !== $errorMessage) {
            if (false !== $pos = strpos($errorMessage, '): ')) {
                // cut "unserialize(%path%):" to make message more readable
                $errorMessage = substr($errorMessage, $pos + 3);
            }

            throw UnserializationFailedException::forValue($serialized, $errorMessage, $errorCode);
        }

        return $value;
    }

    private function __construct()
    {
    }
}
