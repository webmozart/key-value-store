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

/**
 * A countable key-value store.
 *
 * In addition of the properties of a classical store, a countable store
 * has the ability to count its keys.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
interface CountableStore extends KeyValueStore
{
    /**
     * Count the number of keys in the store.
     *
     * @throws ReadException If the store cannot be read.
     */
    public function count();
}
