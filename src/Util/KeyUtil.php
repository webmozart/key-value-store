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

use Webmozart\KeyValueStore\Api\InvalidKeyException;

/**
 * Utility methods for dealing with key-value store keys.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
final class KeyUtil
{
    /**
     * Validates that a key is valid.
     *
     * @param mixed $key The tested key.
     *
     * @throws InvalidKeyException If the key is invalid.
     */
    public static function validate($key)
    {
        if (!is_string($key) && !is_int($key)) {
            throw InvalidKeyException::forKey($key);
        }
    }

    /**
     * Validates that multiple keys are valid.
     *
     * @param array $keys The tested keys.
     *
     * @throws InvalidKeyException If a key is invalid.
     */
    public static function validateMultiple($keys)
    {
        foreach ($keys as $key) {
            if (!is_string($key) && !is_int($key)) {
                throw InvalidKeyException::forKey($key);
            }
        }
    }

    private function __construct()
    {
    }
}
