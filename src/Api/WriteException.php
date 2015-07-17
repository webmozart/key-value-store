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
 * Thrown when a key-value store cannot be written.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class WriteException extends RuntimeException
{
    /**
     * Creates a new exception..
     *
     * @param Exception $exception The exception that caused this exception.
     *
     * @return static The new exception.
     */
    public static function forException(Exception $exception)
    {
        return new static(sprintf(
            'Could not write key-value store: %s',
            $exception->getMessage()
        ), $exception->getCode(), $exception);
    }
}
