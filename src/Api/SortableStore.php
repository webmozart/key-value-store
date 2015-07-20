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
 * A sortable key-value store.
 *
 * In addition of the properties of a classical store, a sortable store
 * has the ability to sort its values by its keys.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
interface SortableStore extends KeyValueStore
{
    /**
     * Sort the store by its keys.
     *
     * The store values will be arranged from lowest to highest when this
     * function has completed.
     *
     * This method accepts an optional second parameter that may be used
     * to modify the sorting behavior using the standard sort flags of PHP.
     *
     * @see http://php.net/manual/en/function.sort.php
     *
     * @param int $flags Sorting type flags (from the standard PHP sort flags).
     *
     * @throws ReadException  If the store cannot be read.
     * @throws WriteException If the store cannot be written.
     */
    public function sort($flags = SORT_REGULAR);
}
