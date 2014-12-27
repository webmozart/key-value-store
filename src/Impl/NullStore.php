<?php

/*
 * This file is part of the webmozart/key-value-store package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\KeyValueStore\Impl;

use Webmozart\KeyValueStore\Assert\Assertion;
use Webmozart\KeyValueStore\KeyValueStore;
use Webmozart\KeyValueStore\Purgeable;

/**
 * A key-value store that does nothing.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class NullStore implements KeyValueStore, Purgeable
{
    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function purge()
    {
    }
}
