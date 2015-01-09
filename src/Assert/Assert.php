<?php

/*
 * This file is part of the webmozart/key-value-store package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\KeyValueStore\Assert;

use InvalidArgumentException;
use Webmozart\KeyValueStore\Api\InvalidKeyException;

/**
 * Contains domain-specific assertions.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Assert
{
    public static function key($key)
    {
        if (!is_string($key) && !is_int($key)) {
            throw new InvalidKeyException(sprintf(
                'Expected a key of type integer or string. Got: %s',
                is_object($key) ? get_class($key) : gettype($key)
            ));
        }
    }

    public static function string($value, $message)
    {
        if (!is_string($value)) {
            throw new InvalidArgumentException(sprintf(
                $message,
                is_object($value) ? get_class($value) : gettype($value)
            ));
        }
    }

    public static function notEmpty($value, $message)
    {
        if (empty($value)) {
            throw new InvalidArgumentException(sprintf(
                $message,
                is_object($value) ? get_class($value) : gettype($value)
            ));
        }
    }

    public static function boolean($value, $message)
    {
        if (!is_bool($value)) {
            throw new InvalidArgumentException(sprintf(
                $message,
                is_object($value) ? get_class($value) : gettype($value)
            ));
        }
    }

    private function __construct() {}
}
