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

use Webmozart\KeyValueStore\Api\InvalidKeyException;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Assertion
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

    private function __construct() {}
}
